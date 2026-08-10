<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Kirim Push Notification ke FCM Google
     */
    public static function sendPushNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $serverKey = env('FCM_SERVER_KEY');
        if (!$serverKey) {
            Log::warning('FCM_SERVER_KEY belum diatur di .env');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to'           => $fcmToken,
                'priority'     => 'high',
                'notification' => [
                    'title'        => $title,
                    'body'         => $body,
                    'sound'        => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => array_merge([
                    'title' => $title,
                    'body'  => $body,
                ], $data),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim Push Notification ke banyak user sekaligus
     */
    public static function broadcastPushNotification(array $fcmTokens, string $title, string $body, array $data = []): void
    {
        foreach ($fcmTokens as $token) {
            if (!empty($token)) {
                self::sendPushNotification($token, $title, $body, $data);
            }
        }
    }
}
