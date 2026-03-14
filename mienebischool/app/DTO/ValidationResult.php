<?php

namespace App\DTO;

class ValidationResult
{
    public bool $isValid;
    public array $errors;    // ValidationError[]
    public array $warnings;  // ValidationError[]

    public function __construct(
        bool $isValid,
        array $errors = [],
        array $warnings = []
    ) {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'errors' => array_map(fn($e) => $e->toArray(), $this->errors),
            'warnings' => array_map(fn($w) => $w->toArray(), $this->warnings),
        ];
    }
}
