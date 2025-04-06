<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineNotificationService
{
    private $channelAccessToken;
    private $channelSecret;

    public function __construct(string $channelAccessToken, string $channelSecret)
    {
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;
    }

    public function sendNotification(string $userId, string $message)
    {
        try {
            Log::info("Attempting to send LINE notification to userId: {$userId}");
            Log::debug("LINE Channel Access Token: " . substr($this->channelAccessToken, 0, 10) . "...");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channelAccessToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.line.me/v2/bot/message/push', [
                'to' => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info("LINE notification sent successfully");
                return true;
            } else {
                Log::error("LINE API error: " . $response->status() . " - " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception when sending LINE notification: " . $e->getMessage());
            return false;
        }
    }
}
