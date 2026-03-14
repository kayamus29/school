<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkSMSService
{
    protected $baseUrl;
    protected $apiToken;
    protected $senderId;

    public function __construct()
    {
        $settings = null;

        try {
            $settings = class_exists(SiteSetting::class) ? SiteSetting::query()->first() : null;
        } catch (\Throwable $e) {
            $settings = null;
        }

        $this->baseUrl = rtrim($settings->bulksms_base_url ?? config('services.bulksms.base_url'), '/');
        $this->apiToken = $settings->bulksms_api_token ?? config('services.bulksms.api_token');
        $this->senderId = $settings->bulksms_sender_id ?? config('services.bulksms.sender_id');
    }

    public function sendSMS(string $to, string $message, ?string $senderId = null): array
    {
        if (!$this->baseUrl || !$this->apiToken || !($senderId ?? $this->senderId)) {
            throw new \RuntimeException('Bulk SMS settings are incomplete. Update the Bulk SMS settings first.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept' => 'application/json',
        ])
            ->timeout(30)
            ->post($this->baseUrl . '/v2/sms', [
                'from' => $senderId ?? $this->senderId,
                'to' => $to,
                'body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();

            Log::info('SMS sent successfully', [
                'message_id' => data_get($data, 'data.id'),
                'cost' => data_get($data, 'data.cost'),
                'recipient' => $to,
            ]);

            return is_array($data) ? $data : [];
        }

        Log::error('SMS sending failed', [
            'status' => $response->status(),
            'error' => $response->json(),
            'recipient' => $to,
        ]);

        throw new \RuntimeException(
            'Failed to send SMS: ' . $response->json('message', 'Unknown error')
        );
    }

    public function sendBulkSMS(array $recipients, string $message, ?string $senderId = null): array
    {
        $results = [
            'total' => count($recipients),
            'successful' => 0,
            'failed' => 0,
            'results' => [],
        ];

        foreach ($recipients as $recipient) {
            try {
                $result = $this->sendSMS($recipient, $message, $senderId);
                $results['successful']++;
                $results['results'][] = [
                    'recipient' => $recipient,
                    'status' => 'success',
                    'data' => $result,
                ];
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['results'][] = [
                    'recipient' => $recipient,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
