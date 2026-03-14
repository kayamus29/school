<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\CsvImportContext;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Adapter for importing school classes.
 */
class SchoolClassImportAdapter implements CsvImportAdapterInterface
{
    protected CsvValidator $validator;

    public function __construct(CsvValidator $validator)
    {
        $this->validator = $validator;
    }

    public function getRequiredColumns(): array
    {
        return ['class_name', 'session_id'];
    }

    public function getOptionalColumns(): array
    {
        return ['is_final_grade'];
    }

    public function validateRow(array $row, int $lineNumber, CsvImportContext $context): ValidationResult
    {
        $errors = [];
        $warnings = [];
        $data = $this->mapRowToData($row);

        // Required checks
        foreach ($this->getRequiredColumns() as $field) {
            if ($error = $this->validator->validateRequired($data[$field] ?? null, $lineNumber, $field)) {
                $errors[] = $error;
            }
        }

        if (!empty($errors)) {
            return new ValidationResult(false, $errors, $warnings);
        }

        // Foreign key check: session_id
        if ($error = $this->validator->validateForeignKey(SchoolSession::class, $data['session_id'], $lineNumber, 'session_id')) {
            $errors[] = $error;
        }

        // CSV-level uniqueness
        $uniqueKey = "{$data['class_name']}_{$data['session_id']}";
        if ($error = $this->validator->validateUniqueInCsv('class_session', $uniqueKey, $lineNumber, $context)) {
            $errors[] = $error;
        }

        // Database-level uniqueness
        $exists = SchoolClass::where('class_name', $data['class_name'])
            ->where('session_id', $data['session_id'])
            ->exists();

        if ($exists) {
            $errors[] = new \App\DTO\ValidationError(
                $lineNumber,
                'class_name',
                $data['class_name'],
                "Class '{$data['class_name']}' already exists in this session.",
                'error'
            );
        }

        return new ValidationResult(empty($errors), $errors, $warnings);
    }

    public function buildModel(array $row): Model
    {
        $data = $this->mapRowToData($row);
        return new SchoolClass([
            'class_name' => $data['class_name'],
            'session_id' => $data['session_id'],
            'is_final_grade' => filter_var($data['is_final_grade'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function persist(Model $model): Model
    {
        $model->save();
        return $model;
    }

    public function getExampleRow(): array
    {
        return [
            'class_name' => 'Grade 10',
            'session_id' => '1',
            'is_final_grade' => 'false',
        ];
    }

    protected function mapRowToData(array $row): array
    {
        $headers = array_merge($this->getRequiredColumns(), $this->getOptionalColumns());
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? '';
        }
        return $data;
    }
}
