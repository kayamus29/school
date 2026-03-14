<?php

namespace App\DTO;

class ValidationError
{
    public int $line;
    public string $field;
    public mixed $value;
    public string $message;
    public string $severity; // 'error' or 'warning'

    public function __construct(
        int $line,
        string $field,
        mixed $value,
        string $message,
        string $severity = 'error'
    ) {
        $this->line = $line;
        $this->field = $field;
        $this->value = $value;
        $this->message = $message;
        $this->severity = $severity;
    }

    public function toArray(): array
    {
        return [
            'line' => $this->line,
            'field' => $this->field,
            'value' => $this->value,
            'message' => $this->message,
            'severity' => $this->severity,
        ];
    }
}
