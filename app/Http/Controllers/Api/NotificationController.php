<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ── USER: Update FCM Token ──────────────────────────────────
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Token berhasil diperbarui.',
        ]);
    }

    // ── ADMIN: Kirim Pemberitahuan ─────────────────────────────
    public function send(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'message'     => 'required|string',
            'target_type' => 'required|in:all,specific',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $createdCount = 0;

        if ($request->target_type === 'all') {
            // Broadcast ke semua user (user_id = null)
            Notification::create([
                'user_id' => null,
                'title'   => $request->title,
                'message' => $request->message,
            ]);
            $createdCount = User::count();

            // Push FCM ke semua user yang punya fcm_token
            $fcmTokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            \App\Services\FcmService::broadcastPushNotification($fcmTokens, $request->title, $request->message);
        } else {
            // Target user tertentu
            $userIds = $request->user_ids ?? [];
            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal satu pengguna untuk pengiriman khusus.',
                ], 422);
            }

            $users = User::whereIn('id', $userIds)->get();
            foreach ($users as $u) {
                Notification::create([
                    'user_id' => $u->id,
                    'title'   => $request->title,
                    'message' => $request->message,
                ]);
                $createdCount++;

                if (!empty($u->fcm_token)) {
                    \App\Services\FcmService::sendPushNotification($u->fcm_token, $request->title, $request->message);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Pemberitahuan berhasil dikirim ke {$createdCount} pengguna.",
        ], 201);
    }

    // ── ADMIN: Riwayat Pemberitahuan ───────────────────────────
    public function indexAdmin(Request $request)
    {
        $notifications = Notification::with('user:id,name,phone')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    // ── ADMIN: Hapus Pemberitahuan ────────────────────────────
    public function destroy(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pemberitahuan berhasil dihapus.',
        ]);
    }

    // ── USER: Ambil Pemberitahuan untuk Pengguna Saat Ini ─────
    public function indexUser(Request $request)
    {
        $userId = $request->user()->id;

        $notifications = Notification::whereNull('user_id')
            ->orWhere('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    // ── USER: Tandai Dibaca ───────────────────────────────────
    public function markAsRead(Request $request, $id)
    {
        $userId = $request->user()->id;
        $notification = Notification::where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        })->findOrFail($id);

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Pemberitahuan ditandai sudah dibaca.',
        ]);
    }
}
