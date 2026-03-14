<?php

namespace App\DTO;

class CsvImportContext
{
    public array $seen = [];         // Track unique values in CSV ['field' => ['value' => line_number]]
    public int $currentLine = 0;     // Current line being processed
    public string $schoolId;         // Current school context
    public int $userId;              // User performing import
    public string $adapterClass;     // Adapter being used
    public string $fileName;         // Original file name

    public function __construct(
        string $schoolId,
        int $userId,
        string $adapterClass,
        string $fileName
    ) {
        $this->schoolId = $schoolId;
        $this->userId = $userId;
        $this->adapterClass = $adapterClass;
        $this->fileName = $fileName;
    }

    /**
     * Track a value as seen for uniqueness validation.
     *
     * @param string $field
     * @param mixed $value
     * @param int $line
     * @return void
     */
    public function markAsSeen(string $field, mixed $value, int $line): void
    {
        if (!isset($this->seen[$field])) {
            $this->seen[$field] = [];
        }

        $this->seen[$field][$value] = $line;
    }

    /**
     * Check if a value has been seen before.
     *
     * @param string $field
     * @param mixed $value
     * @return int|null Line number where first seen, or null if not seen
     */
    public function getFirstSeenLine(string $field, mixed $value): ?int
    {
        return $this->seen[$field][$value] ?? null;
    }

    /**
     * Check if a value has been seen before.
     *
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    public function hasSeen(string $field, mixed $value): bool
    {
        return isset($this->seen[$field][$value]);
    }
}
