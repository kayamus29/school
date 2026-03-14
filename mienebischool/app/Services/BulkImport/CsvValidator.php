<?php

namespace App\Services\BulkImport;

use App\DTO\ValidationError;
use App\DTO\CsvImportContext;
use Illuminate\Support\Facades\DB;

/**
 * Reusable CSV validation rules.
 * Provides common validation logic for CSV imports.
 */
class CsvValidator
{
    /**
     * Validate email format.
     *
     * @param string $email
     * @param int $line
     * @return ValidationError|null
     */
    public function validateEmail(string $email, int $line): ?ValidationError
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new ValidationError(
                line: $line,
                field: 'email',
                value: $email,
                message: 'Invalid email format',
                severity: 'error'
            );
        }

        return null;
    }

    /**
     * Validate foreign key existence.
     *
     * @param string $model Fully qualified model class name
     * @param mixed $id
     * @param int $line
     * @param string $field
     * @return ValidationError|null
     */
    public function validateForeignKey(string $model, mixed $id, int $line, string $field): ?ValidationError
    {
        if (!$model::find($id)) {
            return new ValidationError(
                line: $line,
                field: $field,
                value: $id,
                message: "{$field} ID {$id} not found in current school",
                severity: 'error'
            );
        }

        return null;
    }

    /**
     * Validate database-level uniqueness.
     *
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param int $line
     * @return ValidationError|null
     */
    public function validateUnique(string $table, string $column, mixed $value, int $line): ?ValidationError
    {
        if (DB::table($table)->where($column, $value)->exists()) {
            return new ValidationError(
                line: $line,
                field: $column,
                value: $value,
                message: "{$column} '{$value}' already exists in database",
                severity: 'error'
            );
        }

        return null;
    }

    /**
     * Validate CSV-level uniqueness (duplicates within file).
     *
     * @param string $field
     * @param mixed $value
     * @param int $line
     * @param CsvImportContext $context
     * @return ValidationError|null
     */
    public function validateUniqueInCsv(string $field, mixed $value, int $line, CsvImportContext $context): ?ValidationError
    {
        $firstSeenLine = $context->getFirstSeenLine($field, $value);

        if ($firstSeenLine !== null) {
            return new ValidationError(
                line: $line,
                field: $field,
                value: $value,
                message: "Duplicate {$field} '{$value}' found within CSV (first seen at line {$firstSeenLine})",
                severity: 'error'
            );
        }

        // Mark as seen
        $context->markAsSeen($field, $value, $line);

        return null;
    }

    /**
     * Validate date format.
     *
     * @param string $date
     * @param int $line
     * @param string $field
     * @return ValidationError|null
     */
    public function validateDate(string $date, int $line, string $field): ?ValidationError
    {
        if (!strtotime($date)) {
            return new ValidationError(
                line: $line,
                field: $field,
                value: $date,
                message: "Invalid date format for {$field}",
                severity: 'error'
            );
        }

        return null;
    }

    /**
     * Validate enum value.
     *
     * @param string $value
     * @param array $allowed
     * @param int $line
     * @param string $field
     * @return ValidationError|null
     */
    public function validateEnum(string $value, array $allowed, int $line, string $field): ?ValidationError
    {
        if (!in_array($value, $allowed)) {
            $allowedStr = implode(', ', $allowed);
            return new ValidationError(
                line: $line,
                field: $field,
                value: $value,
                message: "Invalid {$field}. Must be one of: {$allowedStr}",
                severity: 'error'
            );
        }

        return null;
    }

    /**
     * Validate required field is not empty.
     *
     * @param mixed $value
     * @param int $line
     * @param string $field
     * @return ValidationError|null
     */
    public function validateRequired(mixed $value, int $line, string $field): ?ValidationError
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return new ValidationError(
                line: $line,
                field: $field,
                value: $value,
                message: "{$field} is required",
                severity: 'error'
            );
        }

        return null;
    }

    /**
     * Validate phone number format.
     *
     * @param string $phone
     * @param int $line
     * @return ValidationError|null
     */
    public function validatePhone(string $phone, int $line): ?ValidationError
    {
        $pattern = config('features.validation.phone_regex', '/^[0-9]{10,15}$/');

        if (!preg_match($pattern, $phone)) {
            return new ValidationError(
                line: $line,
                field: 'phone',
                value: $phone,
                message: 'Phone number format unusual',
                severity: 'warning'
            );
        }

        return null;
    }
}
