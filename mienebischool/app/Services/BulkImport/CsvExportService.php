<?php

namespace App\Services\BulkImport;

use App\Interfaces\CsvImportAdapterInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Entity-agnostic CSV export service.
 * 
 * Responsibilities:
 * - Generate CSV templates with example data
 * - Export existing data to CSV
 * - Stream large datasets efficiently
 * - Reuse adapter logic for column mapping
 */
class CsvExportService
{
    /**
     * Generate CSV template for an adapter.
     *
     * @param string $adapterClass
     * @return StreamedResponse
     */
    public function generateTemplate(string $adapterClass): StreamedResponse
    {
        $this->verifyExportPermission();

        if (!class_exists($adapterClass) || !in_array(CsvImportAdapterInterface::class, class_implements($adapterClass))) {
            throw new \InvalidArgumentException("Invalid adapter class: {$adapterClass}");
        }

        $adapter = app($adapterClass);

        $fileName = $this->generateFileName($adapterClass, 'template');

        return response()->streamDownload(function () use ($adapter) {
            $file = fopen('php://output', 'w');

            // Write headers
            $headers = array_merge(
                $adapter->getRequiredColumns(),
                $adapter->getOptionalColumns()
            );
            fputcsv($file, $headers);

            // Write example row
            $exampleRow = $adapter->getExampleRow();
            $orderedExample = [];
            foreach ($headers as $header) {
                $orderedExample[] = $exampleRow[$header] ?? '';
            }
            fputcsv($file, $orderedExample);

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Export data using adapter.
     *
     * @param string $adapterClass
     * @param callable $queryBuilder Callback that returns query builder
     * @param array $filters Optional filters
     * @return StreamedResponse
     */
    public function export(string $adapterClass, callable $queryBuilder, array $filters = []): StreamedResponse
    {
        $this->verifyExportPermission();

        if (!class_exists($adapterClass) || !in_array(CsvImportAdapterInterface::class, class_implements($adapterClass))) {
            throw new \InvalidArgumentException("Invalid adapter class: {$adapterClass}");
        }

        $adapter = app($adapterClass);
        $fileName = $this->generateFileName($adapterClass, 'export');

        // Audit log
        $this->logExport($adapterClass, $fileName);

        return response()->streamDownload(function () use ($adapter, $queryBuilder, $filters) {
            $file = fopen('php://output', 'w');

            // Write headers
            $headers = array_merge(
                $adapter->getRequiredColumns(),
                $adapter->getOptionalColumns()
            );
            fputcsv($file, $headers);

            // Stream data in chunks
            $query = $queryBuilder($filters);
            $chunkSize = config('features.csv_export.stream_chunk_size', 500);

            $query->chunk($chunkSize, function ($records) use ($file, $headers, $adapter) {
                foreach ($records as $record) {
                    $row = $this->mapRecordToRow($record, $headers, $adapter);
                    fputcsv($file, $row);
                }
            });

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Map database record to CSV row.
     * 
     * Note: Adapters define import logic. For export, we reverse-engineer
     * the mapping based on column names and model attributes.
     *
     * @param mixed $record
     * @param array $headers
     * @param CsvImportAdapterInterface $adapter
     * @return array
     */
    protected function mapRecordToRow($record, array $headers, CsvImportAdapterInterface $adapter): array
    {
        $row = [];

        foreach ($headers as $header) {
            // Try direct attribute access
            if (isset($record->$header)) {
                $row[] = $record->$header;
            }
            // Try relationship access (e.g., 'class_id' from 'class.id')
            elseif (str_contains($header, '_id') && method_exists($record, str_replace('_id', '', $header))) {
                $relation = str_replace('_id', '', $header);
                $row[] = $record->$relation->id ?? '';
            }
            // Default empty
            else {
                $row[] = '';
            }
        }

        return $row;
    }

    /**
     * Generate filename for export.
     *
     * @param string $adapterClass
     * @param string $type 'template' or 'export'
     * @return string
     */
    protected function generateFileName(string $adapterClass, string $type): string
    {
        $baseName = strtolower(class_basename($adapterClass));
        $baseName = str_replace(['import', 'adapter'], '', $baseName);
        $baseName = trim($baseName, '_');

        $date = date('Y-m-d');

        return "{$baseName}_{$type}_{$date}.csv";
    }

    /**
     * Verify user has export permission.
     *
     * @return void
     * @throws \Exception
     */
    protected function verifyExportPermission(): void
    {
        if (!Auth::check()) {
            throw new \Exception('No authenticated user for CSV export');
        }

        $user = Auth::user();

        if (!$user->can('export_csv')) {
            throw new \Exception('User not authorized to export CSV');
        }
    }

    /**
     * Log export operation.
     *
     * @param string $adapterClass
     * @param string $fileName
     * @return void
     */
    protected function logExport(string $adapterClass, string $fileName): void
    {
        if (!config('features.csv_import.security.log_imports', true)) {
            return;
        }

        Log::info('CSV Export', [
            'school' => config('app.name'),
            'database' => DB::getDatabaseName(),
            'user_id' => Auth::id(),
            'adapter' => class_basename($adapterClass),
            'file_name' => $fileName,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
