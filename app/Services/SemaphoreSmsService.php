<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreSmsService
{
    protected string $apiKey;
    protected string $senderName;
    protected string $endpoint = 'https://api.semaphore.co/api/v4/messages';

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.api_key');
        $this->senderName = config('services.semaphore.sender_name');
    }

    /**
     * Send one message to many numbers.
     * Semaphore accepts a comma-separated list of numbers per request,
     * capped at 1000 recipients per call, so we chunk defensively at 300.
     *
     * @param array<string> $numbers e.g. ['09171234567', '09181234567']
     * @return array{sent: int, failed: int, errors: array}
     */
    public function sendBulk(array $numbers, string $message): array
    {
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach (array_chunk(array_unique(array_filter($numbers)), 300) as $chunk) {
            $response = Http::asForm()->post($this->endpoint, [
                'apikey'     => $this->apiKey,
                'number'     => implode(',', $chunk),
                'message'    => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                $sent += count($chunk);
            } else {
                $failed += count($chunk);
                $errors[] = $response->body();
                Log::error('Semaphore SMS batch failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'errors' => $errors];
    }
}