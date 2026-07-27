<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService {

    private static string $apiUrl = 'https://api.fonnte.com/send';

    /**
     * Kirim OTP ke nomor WhatsApp via Fonnte
     */
    public static function sendOtp(string $phone, string $otp, string $type = 'register'): bool {
        $action = $type === 'forgot_password' ? 'reset sandi' : 'pendaftaran akun';

        $message = "🔐 *Kode OTP Invoice App*\n\n"
                 . "Kode OTP $action Anda:\n\n"
                 . "✨ *$otp* ✨\n\n"
                 . "Berlaku selama *5 menit*.\n"
                 . "Jangan bagikan kode ini kepada siapapun.\n\n"
                 . "_Invoice App_";

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->post(self::$apiUrl, [
                'target'  => self::formatPhone($phone),
                'message' => $message,
            ]);

            $result = $response->json();
            Log::info('Fonnte send OTP', ['phone' => $phone, 'result' => $result]);

            return $response->successful() && ($result['status'] ?? false) === true;
        } catch (\Throwable $e) {
            Log::error('Fonnte error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format nomor ke format Fonnte (tanpa +, tanpa 0 di depan)
     * Contoh: 08123456789 → 628123456789
     *         +62812345 → 62812345
     */
    public static function formatPhone(string $phone): string {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Hapus karakter non-angka
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}
