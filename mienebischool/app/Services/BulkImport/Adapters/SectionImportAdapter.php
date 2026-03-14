<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\CsvImportContext;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Adapter for importing class sections.
 */
class SectionImportAdapter implements CsvImportAdapterInterface
{
    protected CsvValidator $validator;

    public function __construct(CsvValidator $validator)
    {
        $this->validator = $validator;
    }

    public function getRequiredColumns(): array
    {
        return ['section_name', 'class_id', 'session_id'];
    }

    public function getOptionalColumns(): array
    {
        return ['room_no'];
    }

    public function validateRow(array $row, int $lineNumber, CsvImportContext $context): ValidationResult
    {
        $errors = [];
        $warnings = [];
        $data = $this->mapRowToData($row);

        foreach ($this->getRequiredColumns() as $field) {
            if ($error = $this->validator->validateRequired($data[$field] ?? null, $lineNumber, $field)) {
                $errors[] = $error;
            }
        }

        if (!empty($errors)) {
            return new ValidationResult(false, $errors, $warnings);
        }

        // Foreign key checks
        if ($error = $this->validator->validateForeignKey(SchoolClass::class, $data['class_id'], $lineNumber, 'class_id')) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateForeignKey(SchoolSession::class, $data['session_id'], $lineNumber, 'session_id')) {
            $errors[] = $error;
        }

        // Uniqueness check: section_name + class_id
        $exists = Section::where('section_name', $data['section_name'])
            ->where('class_id', $data['class_id'])
            ->exists();

        if ($exists) {
            $errors[] = new \App\DTO\ValidationError(
                $lineNumber,
                'section_name',
                $data['section_name'],
                "Section '{$data['section_name']}' already exists in this class.",
                'error'
            );
        }

        return new ValidationResult(empty($errors), $errors, $warnings);
    }

    public function buildModel(array $row): Model
    {
        $data = $this->mapRowToData($row);
        return new Section([
            'section_name' => $data['section_name'],
            'class_id' => $data['class_id'],
            'session_id' => $data['session_id'],
            'room_no' => $data['room_no'] ?? null,
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
            'section_name' => 'A',
            'class_id' => '1',
            'session_id' => '1',
            'room_no' => 'Room 101',
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
