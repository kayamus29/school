<?php

namespace App\Services\BulkImport;

use App\DTO\ImportResult;
use App\DTO\ValidationError;
use App\DTO\CsvImportContext;
use App\Interfaces\CsvImportAdapterInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Entity-agnostic CSV import orchestrator.
 * 
 * Responsibilities:
 * - Parse CSV files
 * - Validate file structure
 * - Verify school context
 * - Delegate to entity adapters
 * - Manage transaction lifecycle
 * - Return standardized results
 * 
 * Does NOT contain entity-specific logic.
 */
class CsvImportService
{
    /**
     * Import CSV file using specified adapter.
     *
     * @param UploadedFile $file
     * @param string $adapterClass Fully qualified adapter class name
     * @param bool $dryRun If true, rollback after validation (no persist)
     * @return ImportResult
     * @throws \Exception
     */
    public function import(UploadedFile $file, string $adapterClass, bool $dryRun = false): ImportResult
    {
        // Step 1: Security & validation
        $this->verifySchoolContext();
        $this->validateFile($file);

        // Step 2: Initialize adapter
        if (!class_exists($adapterClass) || !in_array(CsvImportAdapterInterface::class, class_implements($adapterClass))) {
            throw new \InvalidArgumentException("Invalid adapter class: {$adapterClass}");
        }

        $adapter = app($adapterClass);

        // Step 3: Parse CSV
        $rows = $this->parseCsv($file);
        $this->validateHeaders($rows, $adapter);

        // Step 4: Initialize context
        $context = new CsvImportContext(
            schoolId: config('app.name'),
            userId: Auth::id(),
            adapterClass: $adapterClass,
            fileName: $file->getClientOriginalName()
        );

        // Step 5: Determine import mode
        $atomic = config('features.csv_import.atomic', true);

        // Step 6: Process rows
        return $this->processRows($rows, $adapter, $context, $dryRun, $atomic);
    }

    /**
     * Process CSV rows with validation and optional persistence.
     *
     * @param array $rows
     * @param CsvImportAdapterInterface $adapter
     * @param CsvImportContext $context
     * @param bool $dryRun
     * @param bool $atomic
     * @return ImportResult
     */
    protected function processRows(
        array $rows,
        CsvImportAdapterInterface $adapter,
        CsvImportContext $context,
        bool $dryRun,
        bool $atomic
    ): ImportResult {
        $allErrors = [];
        $allWarnings = [];
        $successCount = 0;

        // Start transaction if atomic mode
        if ($atomic) {
            DB::beginTransaction();
        }

        try {
            // Remove header row
            $dataRows = array_slice($rows, 1);

            foreach ($dataRows as $index => $row) {
                $lineNumber = $index + 2; // +2 because: 1-indexed + header row
                $context->currentLine = $lineNumber;

                // Phase 1: Business validation (no DB writes)
                $validationResult = $adapter->validateRow($row, $lineNumber, $context);

                if ($validationResult->hasErrors()) {
                    $allErrors = array_merge($allErrors, $validationResult->errors);

                    if ($atomic) {
                        // Atomic mode: collect all errors, continue validation
                        continue;
                    } else {
                        // Partial mode: skip this row, continue with next
                        continue;
                    }
                }

                // Collect warnings
                if ($validationResult->hasWarnings()) {
                    $allWarnings = array_merge($allWarnings, $validationResult->warnings);
                }

                // Phase 2: Build model (no DB writes, no observers)
                $model = $adapter->buildModel($row);

                // Phase 3: Model-level validation (dry-run safe)
                if ($dryRun) {
                    $modelErrors = $this->validateModel($model, $lineNumber);
                    if (!empty($modelErrors)) {
                        $allErrors = array_merge($allErrors, $modelErrors);
                        continue;
                    }
                }

                // Phase 4: Persist (only if not dry-run)
                if (!$dryRun) {
                    try {
                        $adapter->persist($model);
                        $successCount++;
                    } catch (\Exception $e) {
                        $allErrors[] = new ValidationError(
                            line: $lineNumber,
                            field: 'persistence',
                            value: null,
                            message: "Failed to save: " . $e->getMessage(),
                            severity: 'error'
                        );

                        if ($atomic) {
                            // Atomic mode: stop on first persistence error
                            break;
                        }
                    }
                } else {
                    // Dry-run: count as success if validation passed
                    $successCount++;
                }
            }

            // Determine final status and commit/rollback
            $status = $this->determineFinalStatus($dryRun, $atomic, $allErrors, $successCount, count($dataRows));

            if ($atomic && ($dryRun || !empty($allErrors))) {
                DB::rollBack();
            } elseif ($atomic) {
                DB::commit();
            }

            // Audit log
            $this->logImport($context, $status, count($dataRows), $successCount, count($allErrors));

            return new ImportResult(
                status: $status,
                totalRows: count($dataRows),
                successful: $successCount,
                failed: count($allErrors),
                errors: $allErrors,
                warnings: $allWarnings
            );

        } catch (\Exception $e) {
            if ($atomic) {
                DB::rollBack();
            }

            Log::error('CSV Import Failed', [
                'adapter' => $context->adapterClass,
                'file' => $context->fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate model attributes without saving.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param int $lineNumber
     * @return array ValidationError[]
     */
    protected function validateModel($model, int $lineNumber): array
    {
        $errors = [];

        // If model has validation rules, validate them
        if (method_exists($model, 'getRules')) {
            $validator = Validator::make(
                $model->getAttributes(),
                $model->getRules()
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = new ValidationError(
                        line: $lineNumber,
                        field: 'model',
                        value: null,
                        message: $error,
                        severity: 'error'
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Determine final import status.
     *
     * @param bool $dryRun
     * @param bool $atomic
     * @param array $errors
     * @param int $successCount
     * @param int $totalRows
     * @return string
     */
    protected function determineFinalStatus(bool $dryRun, bool $atomic, array $errors, int $successCount, int $totalRows): string
    {
        if ($dryRun) {
            return 'preview';
        }

        if ($atomic) {
            return empty($errors) ? 'success' : 'failed';
        }

        // Partial mode
        if (empty($errors)) {
            return 'success';
        } elseif ($successCount > 0) {
            return 'partial';
        } else {
            return 'failed';
        }
    }

    /**
     * Parse CSV file into array of rows.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $encoding = config('features.csv_import.encoding', 'UTF-8');
        $delimiter = config('features.csv_import.delimiter', ',');

        // Detect and handle BOM
        $content = file_get_contents($path);
        $content = $this->removeBom($content);

        // Convert encoding if needed
        if ($encoding !== 'UTF-8' && mb_detect_encoding($content, 'UTF-8', true) === false) {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // Parse CSV
        $rows = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $row = str_getcsv($line, $delimiter);
            $rows[] = $row;
        }

        if (empty($rows)) {
            throw new \Exception('CSV file is empty');
        }

        return $rows;
    }

    /**
     * Remove BOM from string.
     *
     * @param string $content
     * @return string
     */
    protected function removeBom(string $content): string
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace("/^$bom/", '', $content);
    }

    /**
     * Validate CSV headers match adapter requirements.
     *
     * @param array $rows
     * @param CsvImportAdapterInterface $adapter
     * @return void
     * @throws \Exception
     */
    protected function validateHeaders(array $rows, CsvImportAdapterInterface $adapter): void
    {
        if (empty($rows)) {
            throw new \Exception('CSV file has no headers');
        }

        $headers = array_map('trim', $rows[0]);
        $required = $adapter->getRequiredColumns();

        $missing = array_diff($required, $headers);

        if (!empty($missing)) {
            throw new \Exception('Missing required columns: ' . implode(', ', $missing));
        }

        // Check for duplicate headers
        $duplicates = array_diff_assoc($headers, array_unique($headers));
        if (!empty($duplicates)) {
            throw new \Exception('Duplicate headers found: ' . implode(', ', array_unique($duplicates)));
        }
    }

    /**
     * Validate uploaded file.
     *
     * @param UploadedFile $file
     * @return void
     * @throws \Exception
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        $maxSize = config('features.csv_import.max_file_size', 10240); // KB
        if ($file->getSize() > $maxSize * 1024) {
            throw new \Exception("File size exceeds maximum allowed ({$maxSize}KB)");
        }

        // Check file extension
        $allowed = config('features.csv_import.allowed_extensions', ['csv', 'txt']);
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowed)) {
            throw new \Exception("Invalid file type. Allowed: " . implode(', ', $allowed));
        }

        // Check row count (prevent memory issues)
        $maxRows = config('features.csv_import.max_rows', 5000);
        $lineCount = count(file($file->getRealPath()));

        if ($lineCount > $maxRows) {
            throw new \Exception("File exceeds maximum row limit ({$maxRows} rows)");
        }
    }

    /**
     * Verify school context before import.
     *
     * @return void
     * @throws \Exception
     */
    protected function verifySchoolContext(): void
    {
        if (!config('features.csv_import.security.verify_database', true)) {
            return;
        }

        // 1. Verify database connection
        $expectedDb = config('database.connections.mysql.database');
        $actualDb = DB::connection()->getDatabaseName();

        if ($expectedDb !== $actualDb) {
            throw new \Exception("Database mismatch. Expected: {$expectedDb}, Actual: {$actualDb}");
        }

        // 2. Verify authenticated user
        if (!Auth::check()) {
            throw new \Exception('No authenticated user for CSV import');
        }

        $user = Auth::user();

        // 3. Verify user has school context (if applicable)
        if (method_exists($user, 'school_id') && property_exists($user, 'school_id') && !$user->school_id) {
            throw new \Exception('User not bound to a school');
        }

        // 4. Verify permission
        if (!$user->can('import_csv')) {
            throw new \Exception('User not authorized to import CSV');
        }
    }

    /**
     * Log import operation for audit trail.
     *
     * @param CsvImportContext $context
     * @param string $status
     * @param int $totalRows
     * @param int $successful
     * @param int $failed
     * @return void
     */
    protected function logImport(CsvImportContext $context, string $status, int $totalRows, int $successful, int $failed): void
    {
        if (!config('features.csv_import.security.log_imports', true)) {
            return;
        }

        Log::info('CSV Import Completed', [
            'school' => $context->schoolId,
            'database' => DB::getDatabaseName(),
            'user_id' => $context->userId,
            'adapter' => class_basename($context->adapterClass),
            'file_name' => $context->fileName,
            'status' => $status,
            'total_rows' => $totalRows,
            'successful' => $successful,
            'failed' => $failed,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
