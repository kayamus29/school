<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\ValidationError;
use App\DTO\CsvImportContext;
use App\Models\AssignedTeacher;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Course;
use App\Models\Semester;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Model;

/**
 * Adapter for importing teacher assignments.
 * 
 * Handles assignment of teachers to courses/sections with proper
 * foreign key validation. Supports both course-based and section-based
 * assignments (course_id and section_id are nullable).
 */
class AssignedTeacherImportAdapter implements CsvImportAdapterInterface
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
        return ['teacher_id', 'semester_id', 'class_id', 'session_id'];
    }

    /**
     * Get optional CSV columns.
     *
     * @return array
     */
    public function getOptionalColumns(): array
    {
        return ['section_id', 'course_id'];
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

        // Foreign key validation: teacher_id (must be a teacher)
        if ($error = $this->validator->validateForeignKey(User::class, $data['teacher_id'], $lineNumber, 'teacher_id')) {
            $errors[] = $error;
        } else {
            // Verify user is actually a teacher
            $user = User::find($data['teacher_id']);
            if ($user && !in_array($user->role, ['Teacher', 'teacher'])) {
                $errors[] = new ValidationError(
                    line: $lineNumber,
                    field: 'teacher_id',
                    value: $data['teacher_id'],
                    message: "User ID {$data['teacher_id']} is not a teacher (role: {$user->role})",
                    severity: 'error'
                );
            }
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

        // Optional foreign key validation: section_id
        if (!empty($data['section_id'])) {
            if ($error = $this->validator->validateForeignKey(Section::class, $data['section_id'], $lineNumber, 'section_id')) {
                $errors[] = $error;
            }
        }

        // Optional foreign key validation: course_id
        if (!empty($data['course_id'])) {
            if ($error = $this->validator->validateForeignKey(Course::class, $data['course_id'], $lineNumber, 'course_id')) {
                $errors[] = $error;
            }
        }

        // Business rule: must have either section_id OR course_id (or both)
        if (empty($data['section_id']) && empty($data['course_id'])) {
            $errors[] = new ValidationError(
                line: $lineNumber,
                field: 'section_id/course_id',
                value: null,
                message: 'Either section_id or course_id (or both) must be provided',
                severity: 'error'
            );
        }

        // CSV-level uniqueness: teacher + semester + class + section + course combination
        $uniqueKey = "{$data['teacher_id']}_{$data['semester_id']}_{$data['class_id']}_{$data['section_id']}_{$data['course_id']}";
        if ($error = $this->validator->validateUniqueInCsv('assignment_combination', $uniqueKey, $lineNumber, $context)) {
            $errors[] = $error;
        }

        // Database-level uniqueness: check if assignment already exists
        if ($this->assignmentExists($data)) {
            $errors[] = new ValidationError(
                line: $lineNumber,
                field: 'teacher_id',
                value: $data['teacher_id'],
                message: "This teacher assignment already exists in the database",
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

        // Create AssignedTeacher instance WITHOUT saving
        $assignment = new AssignedTeacher([
            'teacher_id' => $data['teacher_id'],
            'semester_id' => $data['semester_id'],
            'class_id' => $data['class_id'],
            'session_id' => $data['session_id'],
            'section_id' => !empty($data['section_id']) ? $data['section_id'] : null,
            'course_id' => !empty($data['course_id']) ? $data['course_id'] : null,
        ]);

        // Do NOT call save() here
        // Activity log observer will NOT fire
        return $assignment;
    }

    /**
     * Persist model to database.
     * Only called in real import, never in dry-run.
     *
     * @param Model $assignment
     * @return Model
     */
    public function persist(Model $assignment): Model
    {
        // Save to database
        // Activity log observer will fire here (if enabled)
        $assignment->save();

        return $assignment;
    }

    /**
     * Get example row for template generation.
     *
     * @return array
     */
    public function getExampleRow(): array
    {
        return [
            'teacher_id' => '5',
            'semester_id' => '1',
            'class_id' => '1',
            'session_id' => '1',
            'section_id' => '1',
            'course_id' => '3',
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
     * Check if assignment already exists in database.
     *
     * @param array $data
     * @return bool
     */
    protected function assignmentExists(array $data): bool
    {
        $query = AssignedTeacher::where('teacher_id', $data['teacher_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('class_id', $data['class_id'])
            ->where('session_id', $data['session_id']);

        // Handle nullable section_id and course_id
        if (!empty($data['section_id'])) {
            $query->where('section_id', $data['section_id']);
        } else {
            $query->whereNull('section_id');
        }

        if (!empty($data['course_id'])) {
            $query->where('course_id', $data['course_id']);
        } else {
            $query->whereNull('course_id');
        }

        return $query->exists();
    }
}
