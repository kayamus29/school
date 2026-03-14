<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\CsvImportContext;
use App\Models\User;
use App\Models\StudentAcademicInfo;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * Complex adapter for importing Students.
 * Handles User creation, Academic Info, and initial Enrollment/Promotion.
 */
class StudentImportAdapter implements CsvImportAdapterInterface
{
    protected CsvValidator $validator;

    public function __construct(CsvValidator $validator)
    {
        $this->validator = $validator;
    }

    public function getRequiredColumns(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'gender',
            'class_id',
            'section_id',
            'session_id'
        ];
    }

    public function getOptionalColumns(): array
    {
        return [
            'password',
            'phone',
            'address',
            'birthday',
            'nationality',
            'blood_group',
            'board_reg_no',
            'id_card_number'
        ];
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

        // Email and Uniqueness checks
        if ($error = $this->validator->validateEmail($data['email'], $lineNumber)) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateUniqueInCsv('email', $data['email'], $lineNumber, $context)) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateUnique('users', 'email', $data['email'], $lineNumber)) {
            $errors[] = $error;
        }

        // Foreign keys checks
        if ($error = $this->validator->validateForeignKey(SchoolClass::class, $data['class_id'], $lineNumber, 'class_id')) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateForeignKey(Section::class, $data['section_id'], $lineNumber, 'section_id')) {
            $errors[] = $error;
        }
        if ($error = $this->validator->validateForeignKey(SchoolSession::class, $data['session_id'], $lineNumber, 'session_id')) {
            $errors[] = $error;
        }

        return new ValidationResult(empty($errors), $errors, $warnings);
    }

    /**
     * Build the primary Model (User). 
     * Related models will be handled in persist() to maintain atomicity and primary key satisfaction.
     */
    public function buildModel(array $row): Model
    {
        $data = $this->mapRowToData($row);

        $user = new User([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? 'student123'),
            'role' => 'Student',
            'gender' => $data['gender'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'blood_type' => $data['blood_group'] ?? null,
            'status' => 'active',
        ]);

        // Attach raw CSV data for the other models to the object dynamically
        // so it can be used in persist()
        $user->raw_csv_data = $data;

        return $user;
    }

    /**
     * Persist User, then AcademicInfo, then Promotion.
     */
    public function persist(Model $user): Model
    {
        DB::transaction(function () use ($user) {
            $data = $user->raw_csv_data;
            unset($user->raw_csv_data); // Clean up

            // 1. Save User
            $user->save();
            $user->assignRole('Student');

            // 2. Create Academic Info
            StudentAcademicInfo::create([
                'student_id' => $user->id,
                'board_reg_no' => $data['board_reg_no'] ?? null,
            ]);

            // 3. Create initial Promotion/Enrollment
            Promotion::create([
                'student_id' => $user->id,
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'session_id' => $data['session_id'],
                'id_card_number' => $data['id_card_number'] ?? null,
            ]);
        });

        return $user;
    }

    public function getExampleRow(): array
    {
        return [
            'first_name' => 'Alice',
            'last_name' => 'Student',
            'email' => 'alice@school.com',
            'gender' => 'female',
            'class_id' => '1',
            'section_id' => '1',
            'session_id' => '1',
            'password' => 'student123',
            'phone' => '08000000000',
            'address' => 'Student Address',
            'birthday' => '2015-01-01',
            'nationality' => 'Nigerian',
            'blood_group' => 'A+',
            'board_reg_no' => 'BRN-12345',
            'id_card_number' => 'ID-54321',
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
