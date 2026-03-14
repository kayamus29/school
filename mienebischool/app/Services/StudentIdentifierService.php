<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\SiteSetting;

class StudentIdentifierService
{
    public function getConfiguredFormat(): string
    {
        return SiteSetting::query()->value('student_identifier_format') ?: 'STU/{year}/xxx';
    }

    public function generateForSession(int $sessionId): string
    {
        [$prefix, $suffix, $width] = $this->resolvePatternParts(
            $this->normalizeFormat($this->getConfiguredFormat(), $this->resolveYearForSession($sessionId))
        );

        $nextSequence = $this->getNextSequence($prefix, $suffix);

        return $prefix . str_pad((string) $nextSequence, $width, '0', STR_PAD_LEFT) . $suffix;
    }

    public function previewForSession(int $sessionId): string
    {
        [$prefix, $suffix, $width] = $this->resolvePatternParts(
            $this->normalizeFormat($this->getConfiguredFormat(), $this->resolveYearForSession($sessionId))
        );

        return $prefix . str_repeat('0', $width) . $suffix;
    }

    private function resolveYearForSession(int $sessionId): string
    {
        $sessionName = optional(SchoolSession::find($sessionId))->session_name;

        if ($sessionName && preg_match('/(20\d{2})/', $sessionName, $matches)) {
            return $matches[1];
        }

        return date('Y');
    }

    private function normalizeFormat(string $format, string $year): string
    {
        $normalized = trim($format) !== '' ? trim($format) : 'STU/{year}/xxx';

        if (str_contains($normalized, '{year}')) {
            $normalized = str_replace('{year}', $year, $normalized);
        } elseif (preg_match('/20\d{2}/', $normalized)) {
            $normalized = preg_replace('/20\d{2}/', $year, $normalized, 1);
        } else {
            $normalized .= '/' . $year;
        }

        if (!preg_match('/x+/i', $normalized) && !str_contains($normalized, '{seq}')) {
            $normalized .= '/xxx';
        }

        return $normalized;
    }

    private function resolvePatternParts(string $format): array
    {
        $offset = strpos($format, '{seq}');

        if ($offset !== false) {
            return [
                substr($format, 0, $offset),
                substr($format, $offset + 5),
                3,
            ];
        }

        if (preg_match_all('/x+/i', $format, $matches, PREG_OFFSET_CAPTURE) && !empty($matches[0])) {
            $match = end($matches[0]);
            $placeholder = $match[0];
            $offset = $match[1];
            $width = strlen($placeholder);

            return [
                substr($format, 0, $offset),
                substr($format, $offset + $width),
                $width,
            ];
        }

        return [$format . '/', '', 3];
    }

    private function getNextSequence(string $prefix, string $suffix): int
    {
        $likePattern = $prefix . '%' . $suffix;
        $maxSequence = 0;

        Promotion::query()
            ->where('id_card_number', 'like', $likePattern)
            ->pluck('id_card_number')
            ->each(function ($identifier) use ($prefix, $suffix, &$maxSequence) {
                $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';
                if (preg_match($pattern, $identifier, $matches)) {
                    $maxSequence = max($maxSequence, (int) $matches[1]);
                }
            });

        return $maxSequence + 1;
    }
}
