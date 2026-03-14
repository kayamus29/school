<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\CsvImportContext;
use App\Models\Promotion;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Adapter for mass promoting or enrolling existing students.
 */
class PromotionImportAdapter implements CsvImportAdapterInterface
{
    protected CsvValidator $validator;

    public function __construct(CsvValidator $validator)
    {
        $this->validator = $validator;
    }

    public function getRequiredColumns(): array
    {
        return ['student_id', 'class_id', 'section_id', 'session_id'];
    }

    public function getOptionalColumns(): array
    {
        return ['id_card_number'];
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
        if ($error = $this->validator->validateForeignKey(User::class, $data['student_id'], $lineNumber, 'student_id')) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateForeignKey(SchoolClass::class, $data['class_id'], $lineNumber, 'class_id')) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateForeignKey(Section::class, $data['section_id'], $lineNumber, 'section_id')) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateForeignKey(SchoolSession::class, $data['session_id'], $lineNumber, 'session_id')) {
            $errors[] = $error;
        }

        // Check if student is already promoted in this session
        $exists = Promotion::where('student_id', $data['student_id'])
            ->where('session_id', $data['session_id'])
            ->exists();

        if ($exists) {
            $errors[] = new \App\DTO\ValidationError(
                $lineNumber,
                'student_id',
                $data['student_id'],
                "Student is already enrolled/promoted in this session.",
                'error'
            );
        }

        return new ValidationResult(empty($errors), $errors, $warnings);
    }

    public function buildModel(array $row): Model
    {
        $data = $this->mapRowToData($row);
        return new Promotion([
            'student_id' => $data['student_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'session_id' => $data['session_id'],
            'id_card_number' => $data['id_card_number'] ?? null,
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
            'student_id' => '10',
            'class_id' => '2',
            'section_id' => '5',
            'session_id' => '2',
            'id_card_number' => 'STU-12345',
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
