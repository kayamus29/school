<?php

namespace App\Services\BulkImport\Adapters;

use App\Interfaces\CsvImportAdapterInterface;
use App\Services\BulkImport\CsvValidator;
use App\DTO\ValidationResult;
use App\DTO\ValidationError;
use App\DTO\CsvImportContext;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Reference adapter for importing users (students, teachers, parents, accountants).
 * 
 * This is the gold standard adapter that demonstrates:
 * - Clean buildModel() vs persist() separation
 * - Two-tier uniqueness validation (CSV + database)
 * - Proper use of CsvValidator
 * - Zero assumptions about global state
 * - Handling of Spatie roles
 */
class UserImportAdapter implements CsvImportAdapterInterface
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
        return ['first_name', 'last_name', 'email', 'role', 'gender'];
    }

    /**
     * Get optional CSV columns.
     *
     * @return array
     */
    public function getOptionalColumns(): array
    {
        return ['password', 'phone', 'address', 'birthday', 'nationality', 'blood_group'];
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

        // Email format validation
        if ($error = $this->validator->validateEmail($data['email'], $lineNumber)) {
            $errors[] = $error;
        }

        // CSV-level uniqueness (duplicates within file)
        if ($error = $this->validator->validateUniqueInCsv('email', $data['email'], $lineNumber, $context)) {
            $errors[] = $error;
        }

        // Database-level uniqueness
        if ($error = $this->validator->validateUnique('users', 'email', $data['email'], $lineNumber)) {
            $errors[] = $error;
        }

        // Role validation
        $allowedRoles = config('features.validation.allowed_roles', ['Student', 'Teacher', 'Parent', 'Accountant']);
        if ($error = $this->validator->validateEnum($data['role'], $allowedRoles, $lineNumber, 'role')) {
            $errors[] = $error;
        }

        // Gender validation
        $allowedGenders = config('features.validation.allowed_genders', ['male', 'female']);
        if ($error = $this->validator->validateEnum($data['gender'], $allowedGenders, $lineNumber, 'gender')) {
            $errors[] = $error;
        }

        // Phone validation (optional, warning only)
        if (!empty($data['phone'])) {
            if ($error = $this->validator->validatePhone($data['phone'], $lineNumber)) {
                $warnings[] = $error;
            }
        }

        // Birthday validation (optional)
        if (!empty($data['birthday'])) {
            if ($error = $this->validator->validateDate($data['birthday'], $lineNumber, 'birthday')) {
                $errors[] = $error;
            }
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

        // Create User instance WITHOUT saving
        $user = new User([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? config('features.validation.password_default', 'changeme')),
            'role' => $data['role'],
            'gender' => $data['gender'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'blood_group' => $data['blood_group'] ?? null,
            'status' => 'active',
        ]);

        // Do NOT call save() here
        // Do NOT sync roles here (side effect)

        return $user;
    }

    /**
     * Persist model to database.
     * Only called in real import, never in dry-run.
     *
     * @param Model $user
     * @return Model
     */
    public function persist(Model $user): Model
    {
        // Save to database
        $user->save();

        // Sync Spatie role (side effect - only happens on persist)
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles($user->role);
        }

        return $user;
    }

    /**
     * Get example row for template generation.
     *
     * @return array
     */
    public function getExampleRow(): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role' => 'Student',
            'gender' => 'male',
            'password' => 'changeme',
            'phone' => '08012345678',
            'address' => '123 Main St, Lagos',
            'birthday' => '2010-05-15',
            'nationality' => 'Nigerian',
            'blood_group' => 'O+',
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
}
