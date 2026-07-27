<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpToken;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    // ── Send OTP via WhatsApp (Fonnte) ─────────────────────────
    public function sendOtp(Request $request) {
        $request->validate([
            'phone' => 'required|string',
            'type'  => 'required|in:register,forgot_password',
        ]);

        $phone = FonnteService::formatPhone($request->phone);
        $type  = $request->type;

        if ($type === 'register') {
            $existingUser = User::where('phone', $phone)->first();
            if ($existingUser) {
                return response()->json([
                    'message' => 'Nomor WhatsApp ini sudah terdaftar. Silakan login.',
                ], 422);
            }
        } elseif ($type === 'forgot_password') {
            $user = User::where('phone', $phone)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Nomor WhatsApp belum terdaftar.',
                ], 404);
            }
        }

        // Generate OTP 6 digit
        $otp = (string) rand(100000, 999999);

        // Invalidate OTP lama yang belum dipakai
        OtpToken::where('phone', $phone)
            ->where('type', $type)
            ->where('used', false)
            ->update(['used' => true]);

        // Simpan OTP baru (berlaku 5 menit)
        OtpToken::create([
            'phone'      => $phone,
            'otp'        => $otp,
            'type'       => $type,
            'used'       => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Kirim via Fonnte
        $sent = FonnteService::sendOtp($phone, $otp, $type);

        return response()->json([
            'message' => $sent
                ? 'Kode OTP berhasil dikirim via WhatsApp'
                : 'OTP terbuat tetapi gagal dikirim via WhatsApp (Cek Fonnte token)',
            'phone'   => $phone,
            'status'  => $sent,
        ]);
    }

    // ── Verify OTP ─────────────────────────────────────────────
    public function verifyOtp(Request $request) {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
            'type'  => 'required|in:register,forgot_password',
        ]);

        $phone = FonnteService::formatPhone($request->phone);

        $tokenRecord = OtpToken::where('phone', $phone)
            ->where('type', $request->type)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->orderBy('id', 'desc')
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Kode OTP salah.'], 422);
        }

        if ($tokenRecord->isExpired()) {
            return response()->json(['message' => 'Kode OTP sudah kedaluwarsa.'], 422);
        }

        return response()->json(['message' => 'Kode OTP valid', 'verified' => true]);
    }

    // ── Register dengan Phone + OTP ────────────────────────────
    public function register(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $phone = FonnteService::formatPhone($request->phone);

        // Verifikasi OTP
        $tokenRecord = OtpToken::where('phone', $phone)
            ->where('type', 'register')
            ->where('otp', $request->otp)
            ->where('used', false)
            ->orderBy('id', 'desc')
            ->first();

        if (!$tokenRecord || $tokenRecord->isExpired()) {
            return response()->json(['message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.'], 422);
        }

        // Tandai OTP digunakan
        $tokenRecord->update(['used' => true]);

        // Cek jika phone sudah ada
        if (User::where('phone', $phone)->exists()) {
            return response()->json(['message' => 'Nomor WhatsApp sudah terdaftar.'], 422);
        }

        $user = User::create([
            'name'              => $request->name,
            'phone'             => $phone,
            'email'             => $request->email ?? ($phone . '@phone.local'),
            'phone_verified_at' => now(),
            'password'          => Hash::make($request->password),
        ]);

        // Auto-create bisnis
        $user->businesses()->create([
            'name'      => 'Bisnis ' . $user->name,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // ── Login dengan Phone / Email + Password ─────────────────
    public function login(Request $request) {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->phone);
        $formattedPhone = FonnteService::formatPhone($loginInput);
        $localPhone = str_starts_with($formattedPhone, '62')
            ? '0' . substr($formattedPhone, 2)
            : $formattedPhone;

        // Cari berdasarkan variasi format phone atau email
        $user = User::where('phone', $formattedPhone)
            ->orWhere('phone', $localPhone)
            ->orWhere('phone', '+' . $formattedPhone)
            ->orWhere('phone', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor WhatsApp atau password salah.'],
            ]);
        }

        // Hapus token lama, buat token baru
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    // ── Reset Password via WhatsApp OTP ───────────────────────
    public function resetPassword(Request $request) {
        $request->validate([
            'phone'                 => 'required|string',
            'otp'                   => 'required|string|size:6',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $phone = FonnteService::formatPhone($request->phone);

        $tokenRecord = OtpToken::where('phone', $phone)
            ->where('type', 'forgot_password')
            ->where('otp', $request->otp)
            ->where('used', false)
            ->orderBy('id', 'desc')
            ->first();

        if (!$tokenRecord || $tokenRecord->isExpired()) {
            return response()->json(['message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.'], 422);
        }

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Update password & tandai OTP dipakai
        $user->update(['password' => Hash::make($request->password)]);
        $tokenRecord->update(['used' => true]);

        // Revoke token lama
        $user->tokens()->delete();
        $authToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Password berhasil diubah. Silakan login.',
            'user'    => $user,
            'token'   => $authToken,
        ]);
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    // ── Me ────────────────────────────────────────────────────
    public function me(Request $request) {
        return response()->json($request->user());
    }

    // ── Update Profile ─────────────────────────────────────────
    public function updateProfile(Request $request) {
        $user = $request->user();
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string',
            'email' => 'nullable|email',
        ]);

        if (isset($validated['phone']) && !empty($validated['phone'])) {
            $validated['phone'] = FonnteService::formatPhone($validated['phone']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user'    => $user,
        ]);
    }

    // ── Change Password ───────────────────────────────────────
    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Password berhasil diubah',
            'token'   => $token,
        ]);
    }
}

