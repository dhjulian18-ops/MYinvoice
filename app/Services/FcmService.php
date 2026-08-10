<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Dapatkan OAuth2 Access Token untuk Google FCM HTTP v1 API
     */
    private static function getAccessToken(): ?array
    {
        try {
            $jsonPath = storage_path('app/firebase-service-account.json');
            if (!file_exists($jsonPath)) {
                // Cek dari env B64 (untuk Vercel deployment)
                $b64Key = env('FIREBASE_SERVICE_ACCOUNT_B64');
                if (!$b64Key) {
                    Log::warning('Firebase service account key tidak ditemukan');
                    return null;
                }
                $keyData = json_decode(base64_decode($b64Key), true);
            } else {
                $keyData = json_decode(file_get_contents($jsonPath), true);
            }

            if (!$keyData || !isset($keyData['private_key'], $keyData['client_email'], $keyData['project_id'])) {
                Log::error('Format Firebase Service Account Key tidak valid');
                return null;
            }

            $now = time();
            $payload = [
                'iss'   => $keyData['client_email'],
                'sub'   => $keyData['client_email'],
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            ];

            $jwt = JWT::encode($payload, $keyData['private_key'], 'RS256');

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return [
                    'access_token' => $response->json('access_token'),
                    'project_id'   => $keyData['project_id'],
                ];
            }

            Log::error('Gagal mendapatkan Access Token OAuth2 FCM: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('FCM Access Token Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim Push Notification ke FCM Google (HTTP v1 API)
     */
    public static function sendPushNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $auth = self::getAccessToken();
        if (!$auth) {
            return false;
        }

        try {
            $projectId = $auth['project_id'];
            $accessToken = $auth['access_token'];

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'HIGH',
                        'direct_boot_ok' => true,
                        'ttl' => '0s',
                        'notification' => [
                            'sound'                     => 'default',
                            'channel_id'                => 'high_importance_channel',
                            'default_sound'             => true,
                            'default_vibrate_timings'   => true,
                            'notification_priority'     => 'PRIORITY_MAX',
                            'visibility'                => 'PUBLIC',
                        ],
                    ],
                    'data' => array_merge([
                        'title' => $title,
                        'body'  => $body,
                    ], $data),
                ],
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
