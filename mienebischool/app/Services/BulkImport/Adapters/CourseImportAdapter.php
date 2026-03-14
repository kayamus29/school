<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\ValidationError;
use App\DTO\CsvImportContext;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Adapter for importing courses.
 * 
 * Handles course creation with proper foreign key validation
 * for class_id, semester_id, and session_id.
 */
class CourseImportAdapter implements CsvImportAdapterInterface
{
    protected CsvValidator $validator;

    public function __construct(CsvValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get required CSV columns.
     *
     * @return array
     */
    public function getRequiredColumns(): array
    {
        return ['course_name', 'course_type', 'class_id', 'semester_id', 'session_id'];
    }

    /**
     * Get optional CSV columns.
     *
     * @return array
     */
    public function getOptionalColumns(): array
    {
        return [];
    }

    /**
     * Validate a single CSV row.
     *
     * @param array $row
     * @param int $lineNumber
     * @param CsvImportContext $context
     * @return ValidationResult
     */
    public function validateRow(array $row, int $lineNumber, CsvImportContext $context): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Map row to associative array
        $data = $this->mapRowToData($row);

        // Required field validation
        foreach ($this->getRequiredColumns() as $field) {
            if ($error = $this->validator->validateRequired($data[$field] ?? null, $lineNumber, $field)) {
                $errors[] = $error;
            }
        }

        // If required fields missing, skip further validation
        if (!empty($errors)) {
            return new ValidationResult(false, $errors, $warnings);
        }

        // Course type validation
        $allowedTypes = ['Core', 'Elective', 'Mandatory', 'Optional'];
        if ($error = $this->validator->validateEnum($data['course_type'], $allowedTypes, $lineNumber, 'course_type')) {
            $errors[] = $error;
        }

        // Foreign key validation: class_id
        if ($error = $this->validator->validateForeignKey(SchoolClass::class, $data['class_id'], $lineNumber, 'class_id')) {
            $errors[] = $error;
        }

        // Foreign key validation: semester_id
        if ($error = $this->validator->validateForeignKey(Semester::class, $data['semester_id'], $lineNumber, 'semester_id')) {
            $errors[] = $error;
        }

        // Foreign key validation: session_id
        if ($error = $this->validator->validateForeignKey(SchoolSession::class, $data['session_id'], $lineNumber, 'session_id')) {
            $errors[] = $error;
        }

        // CSV-level uniqueness: course_name + class_id + semester_id combination
        $uniqueKey = "{$data['course_name']}_{$data['class_id']}_{$data['semester_id']}";
        if ($error = $this->validator->validateUniqueInCsv('course_combination', $uniqueKey, $lineNumber, $context)) {
            $errors[] = $error;
        }

        // Database-level uniqueness: check if course already exists for this class/semester
        if ($this->courseExists($data['course_name'], $data['class_id'], $data['semester_id'])) {
            $errors[] = new ValidationError(
                line: $lineNumber,
                field: 'course_name',
                value: $data['course_name'],
                message: "Course '{$data['course_name']}' already exists for this class and semester",
                severity: 'error'
            );
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors,
            warnings: $warnings
        );
    }

    /**
     * Build model instance WITHOUT saving.
     * NO observers, NO events, NO database writes.
     *
     * @param array $row
     * @return Model
     */
    public function buildModel(array $row): Model
    {
        $data = $this->mapRowToData($row);

        // Create Course instance WITHOUT saving
        $course = new Course([
            'course_name' => $data['course_name'],
            'course_type' => $data['course_type'],
            'class_id' => $data['class_id'],
            'semester_id' => $data['semester_id'],
            'session_id' => $data['session_id'],
        ]);

        // Do NOT call save() here
        return $course;
    }

    /**
     * Persist model to database.
     * Only called in real import, never in dry-run.
     *
     * @param Model $course
     * @return Model
     */
    public function persist(Model $course): Model
    {
        // Save to database
        $course->save();

        return $course;
    }

    /**
     * Get example row for template generation.
     *
     * @return array
     */
    public function getExampleRow(): array
    {
        return [
            'course_name' => 'Mathematics',
            'course_type' => 'Core',
            'class_id' => '1',
            'semester_id' => '1',
            'session_id' => '1',
        ];
    }

    /**
     * Map CSV row array to associative array.
     *
     * @param array $row
     * @return array
     */
    protected function mapRowToData(array $row): array
    {
        $headers = array_merge($this->getRequiredColumns(), $this->getOptionalColumns());
        $data = [];

        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? '';
        }

        return $data;
    }

    /**
     * Check if course already exists in database.
     *
     * @param string $courseName
     * @param int $classId
     * @param int $semesterId
     * @return bool
     */
    protected function courseExists(string $courseName, int $classId, int $semesterId): bool
    {
        return Course::where('course_name', $courseName)
            ->where('class_id', $classId)
            ->where('semester_id', $semesterId)
            ->exists();
    }
}
