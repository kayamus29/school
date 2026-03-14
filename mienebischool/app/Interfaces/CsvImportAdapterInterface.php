<?php

namespace App\Interfaces;

use App\DTO\ValidationResult;
use App\DTO\CsvImportContext;
use Illuminate\Database\Eloquent\Model;

interface CsvImportAdapterInterface
{
    /**
     * Get required CSV columns.
     *
     * @return array
     */
    public function getRequiredColumns(): array;

    /**
     * Get optional CSV columns.
     *
     * @return array
     */
    public function getOptionalColumns(): array;

    /**
     * Validate a single CSV row.
     *
     * @param array $row
     * @param int $lineNumber
     * @param CsvImportContext $context
     * @return ValidationResult
     */
    public function validateRow(array $row, int $lineNumber, CsvImportContext $context): ValidationResult;

    /**
     * Build model instance WITHOUT saving (for dry-run).
     * NO observers, NO events, NO database writes.
     *
     * @param array $row
     * @return Model
     */
    public function buildModel(array $row): Model;

    /**
     * Persist model to database (only called in real import).
     *
     * @param Model $model
     * @return Model
     */
    public function persist(Model $model): Model;

    /**
     * Get example row for template generation.
     *
     * @return array
     */
    public function getExampleRow(): array;
}
