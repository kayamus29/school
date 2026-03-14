<?php

namespace App\DTO;

class ImportResult
{
    public string $status;      // 'success', 'failed', 'partial', 'preview'
    public int $totalRows;
    public int $successful;
    public int $failed;
    public array $errors;       // ValidationError[]
    public array $warnings;     // ValidationError[]
    public ?int $importId;      // For audit log

    public function __construct(
        string $status,
        int $totalRows,
        int $successful,
        int $failed,
        array $errors = [],
        array $warnings = [],
        ?int $importId = null
    ) {
        $this->status = $status;
        $this->totalRows = $totalRows;
        $this->successful = $successful;
        $this->failed = $failed;
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->importId = $importId;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isPreview(): bool
    {
        return $this->status === 'preview';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'total_rows' => $this->totalRows,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'errors' => array_map(fn($e) => $e->toArray(), $this->errors),
            'warnings' => array_map(fn($w) => $w->toArray(), $this->warnings),
            'import_id' => $this->importId,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
