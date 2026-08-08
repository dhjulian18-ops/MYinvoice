<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class UserChatController extends Controller
{
    // Get chat messages between current user and admin
    public function getMessages(Request $request)
    {
        $userId = $request->user()->id;

        // Find primary admin user
        $admin = User::where('is_admin', true)->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin tidak ditemukan.'], 404);
        }

        $adminId = $admin->id;

        // Mark messages from admin to user as read
        ChatMessage::where('sender_id', $adminId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where(function ($q) use ($userId, $adminId) {
            $q->where('sender_id', $userId)->where('receiver_id', $adminId);
        })->orWhere(function ($q) use ($userId, $adminId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        $adminData = $admin->toArray();
        $adminData['support_name']     = AppSetting::getValue('support_name', $admin->name);
        $adminData['support_subtitle'] = AppSetting::getValue('support_subtitle', 'Online Support');

        return response()->json([
            'success' => true,
            'admin'   => $adminData,
            'data'    => $messages,
        ]);
    }

    // User sends chat message to admin
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $admin = User::where('is_admin', true)->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin tidak ditemukan.'], 404);
        }

        $msg = ChatMessage::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $admin->id,
            'message'     => $request->message,
            'is_read'     => false,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $msg,
        ]);
    }

    // Get count of unread messages from admin to current user (does NOT mark as read)
    public function getUnreadCount(Request $request)
    {
        $userId = $request->user()->id;

        $admin = User::where('is_admin', true)->first();
        if (!$admin) {
            return response()->json(['success' => true, 'unread_count' => 0]);
        }

        $count = ChatMessage::where('sender_id', $admin->id)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $count,
        ]);
    }
}
