<?php

namespace App\Services;

use App\Models\InboundEmail;
use App\Models\SiteSetting;
use Carbon\Carbon;

class ImapMailboxService
{
    public function sync(int $userId): array
    {
        if (!function_exists('imap_open')) {
            throw new \RuntimeException('PHP IMAP extension is not enabled on this server.');
        }

        $settings = SiteSetting::query()->first();

        if (!$settings || !$settings->imap_host || !$settings->imap_username || !$settings->imap_password) {
            throw new \RuntimeException('IMAP settings are incomplete. Update site settings first.');
        }

        $mailboxPath = $this->buildMailboxPath($settings);
        $stream = @imap_open($mailboxPath, $settings->imap_username, $settings->imap_password);

        if (!$stream) {
            throw new \RuntimeException('Unable to connect to IMAP mailbox: ' . imap_last_error());
        }

        $uids = imap_search($stream, 'ALL', SE_UID) ?: [];
        rsort($uids);

        $synced = 0;

        foreach (array_slice($uids, 0, 100) as $uid) {
            $overviewList = imap_fetch_overview($stream, (string) $uid, FT_UID);
            $overview = $overviewList[0] ?? null;

            if (!$overview) {
                continue;
            }

            $headers = imap_headerinfo($stream, imap_msgno($stream, $uid));
            $messageId = trim((string) ($overview->message_id ?? ''));
            $rawHeader = imap_fetchheader($stream, $uid, FT_UID) ?: null;

            $bodyHtml = $this->extractBody($stream, $uid, 'html');
            $bodyText = $this->extractBody($stream, $uid, 'plain');

            InboundEmail::updateOrCreate(
                [
                    'uid' => (string) $uid,
                    'mailbox' => $settings->imap_mailbox ?? 'INBOX',
                ],
                [
                    'message_id' => $messageId ?: null,
                    'from_name' => $this->decodeHeaderValue($headers->from[0]->personal ?? null),
                    'from_email' => $this->normalizeAddress($headers->from[0] ?? null),
                    'to_email' => $this->normalizeAddress($headers->to[0] ?? null),
                    'subject' => $this->decodeHeaderValue($overview->subject ?? '(No subject)'),
                    'body_text' => $bodyText,
                    'body_html' => $bodyHtml,
                    'received_at' => !empty($overview->date) ? Carbon::parse($overview->date) : now(),
                    'is_seen' => (bool) ($overview->seen ?? false),
                    'raw_headers' => $rawHeader,
                    'metadata' => [
                        'imap_uid' => $uid,
                        'references' => $overview->references ?? null,
                        'in_reply_to' => $overview->in_reply_to ?? null,
                    ],
                    'synced_by' => $userId,
                ]
            );

            $synced++;
        }

        imap_close($stream);

        return ['synced' => $synced];
    }

    protected function buildMailboxPath(SiteSetting $settings): string
    {
        $flags = [$settings->imap_encryption ?: 'ssl'];

        if (!$settings->imap_validate_cert) {
            $flags[] = 'novalidate-cert';
        }

        return sprintf(
            '{%s:%d/imap/%s}%s',
            $settings->imap_host,
            $settings->imap_port ?: 993,
            implode('/', $flags),
            $settings->imap_mailbox ?: 'INBOX'
        );
    }

    protected function normalizeAddress($address): ?string
    {
        if (!$address || empty($address->mailbox) || empty($address->host)) {
            return null;
        }

        return $address->mailbox . '@' . $address->host;
    }

    protected function decodeHeaderValue(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        $elements = imap_mime_header_decode($value);
        $decoded = '';

        foreach ($elements as $element) {
            $charset = strtoupper($element->charset);
            $text = $element->text;

            if ($charset !== 'DEFAULT' && function_exists('iconv')) {
                $converted = @iconv($charset, 'UTF-8//IGNORE', $text);
                $decoded .= $converted !== false ? $converted : $text;
            } else {
                $decoded .= $text;
            }
        }

        return $decoded;
    }

    protected function extractBody($stream, int $uid, string $preferredType): ?string
    {
        $structure = imap_fetchstructure($stream, $uid, FT_UID);

        if (!$structure) {
            return null;
        }

        $targetType = $preferredType === 'html' ? 1 : 0;
        $body = $this->findPart($stream, $uid, $structure, $targetType);

        if ($body) {
            return $body;
        }

        $fallback = imap_body($stream, $uid, FT_UID);

        return is_string($fallback) ? quoted_printable_decode($fallback) : null;
    }

    protected function findPart($stream, int $uid, $structure, int $targetType, string $partNumber = '1'): ?string
    {
        if ((int) ($structure->type ?? -1) === 0 && (int) ($structure->ifsubtype ?? 0) !== 0) {
            $subtype = strtoupper((string) ($structure->subtype ?? ''));
            if (($targetType === 1 && $subtype === 'HTML') || ($targetType === 0 && $subtype === 'PLAIN')) {
                return $this->decodeBody(
                    imap_fetchbody($stream, $uid, $partNumber, FT_UID),
                    (int) ($structure->encoding ?? 0)
                );
            }
        }

        if (!empty($structure->parts)) {
            foreach ($structure->parts as $index => $part) {
                $nestedPartNumber = $partNumber === '1' ? (string) ($index + 1) : $partNumber . '.' . ($index + 1);
                $result = $this->findPart($stream, $uid, $part, $targetType, $nestedPartNumber);

                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    protected function decodeBody($body, int $encoding): ?string
    {
        if (!is_string($body)) {
            return null;
        }

        switch ($encoding) {
            case 3:
                return base64_decode($body) ?: $body;
            case 4:
                return quoted_printable_decode($body);
            default:
                return $body;
        }
    }
}
