<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Client;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ...
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'email'      => 'sometimes|required|string|email|unique:users,email,' . $id,
            'phone'      => 'sometimes|required|string',
            'is_admin'   => 'sometimes|boolean',
            'is_blocked' => 'sometimes|boolean',
        ]);

        if (isset($request->name)) $user->name = $request->name;
        if (isset($request->email)) $user->email = $request->email;
        if (isset($request->phone)) $user->phone = $request->phone;
        if (isset($request->is_admin) && $user->id !== $request->user()->id) {
            $user->is_admin = $request->is_admin;
        }
        if (isset($request->is_blocked) && $user->id !== $request->user()->id) {
            $user->is_blocked = $request->is_blocked;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui.',
            'data'    => $user,
        ]);
    }

    public function toggleBlockUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat memblokir akun sendiri.',
            ], 400);
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $statusText = $user->is_blocked ? 'diblokir' : 'dibuka pemblokirannya';

        return response()->json([
            'success' => true,
            'message' => "Pengguna {$user->name} berhasil {$statusText}.",
            'data'    => $user,
        ]);
    }

    // ── Live Chat (Admin side) ──
    public function getChatUsers(Request $request)
    {
        $adminId = $request->user()->id;

        $users = User::where('id', '!=', $adminId)->get()->map(function ($u) use ($adminId) {
            $lastMsg = ChatMessage::where(function ($q) use ($u, $adminId) {
                $q->where('sender_id', $u->id)->where('receiver_id', $adminId);
            })->orWhere(function ($q) use ($u, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $u->id);
            })->latest()->first();

            $unreadCount = ChatMessage::where('sender_id', $u->id)
                ->where('receiver_id', $adminId)
                ->where('is_read', false)
                ->count();

            $u->last_message = $lastMsg;
            $u->unread_count = $unreadCount;
            return $u;
        });

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function getChatMessages(Request $request, $userId)
    {
        $adminId = $request->user()->id;

        // Mark messages as read
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where(function ($q) use ($userId, $adminId) {
            $q->where('sender_id', $userId)->where('receiver_id', $adminId);
        })->orWhere(function ($q) use ($userId, $adminId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $messages,
        ]);
    }

    public function sendChatMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string',
        ]);

        $msg = ChatMessage::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
            'is_read'     => false,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $msg->load(['sender', 'receiver']),
        ]);
    }
    // ── Dashboard Overview Stats ──
    public function dashboard(Request $request)
    {
        $totalUsers = User::count();
        $totalBusinesses = Business::count();
        $totalClients = Client::count();
        $totalItems = Item::count();
        $totalInvoices = Invoice::count();

        // Calculate total revenue from all invoices
        $totalRevenue = Invoice::with('invoiceItems')->get()->sum(function ($inv) {
            return $inv->total;
        });

        $recentUsers = User::latest()->take(5)->get();
        $recentInvoices = Invoice::with(['client', 'business.user', 'invoiceItems'])->latest()->take(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users'      => $totalUsers,
                'total_businesses' => $totalBusinesses,
                'total_clients'    => $totalClients,
                'total_items'      => $totalItems,
                'total_invoices'   => $totalInvoices,
                'total_revenue'    => $totalRevenue,
                'recent_users'     => $recentUsers,
                'recent_invoices'  => $recentInvoices,
            ]
        ]);
    }

    // ── Management Users ──
    public function getUsers(Request $request)
    {
        $users = User::withCount(['businesses'])->latest()->get()->map(function ($u) {
            $bizIds = $u->businesses->pluck('id');
            $u->clients_count = Client::whereIn('business_id', $bizIds)->count();
            $u->items_count = Item::whereIn('business_id', $bizIds)->count();
            $u->invoices_count = Invoice::whereIn('business_id', $bizIds)->count();
            return $u;
        });

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function toggleAdmin(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent demoting self
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengubah status admin diri sendiri.',
            ], 400);
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Status admin untuk {$user->name} berhasil diperbarui.",
            'data'    => $user,
        ]);
    }

    public function deleteUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri.',
            ], 400);
        }

        // Cascade delete user's data
        foreach ($user->businesses as $biz) {
            $biz->clients()->delete();
            $biz->items()->delete();
            $biz->invoices()->delete();
            $biz->delete();
        }
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna dan seluruh datanya berhasil dihapus.',
        ]);
    }

    // ── Management Invoices ──
    public function getInvoices(Request $request)
    {
        $invoices = Invoice::with(['client', 'business.user', 'invoiceItems'])->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $invoices,
        ]);
    }

    public function deleteInvoice(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dihapus.',
        ]);
    }

    // ── Management Clients ──
    public function getClients(Request $request)
    {
        $clients = Client::with(['business.user'])->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }

    public function deleteClient(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Klien berhasil dihapus.',
        ]);
    }

    // ── Management Items ──
    public function getItems(Request $request)
    {
        $items = Item::with(['business.user'])->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    public function deleteItem(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus.',
        ]);
    }

    // ── Management Businesses ──
    public function getBusinesses(Request $request)
    {
        $businesses = Business::with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $businesses,
        ]);
    }
}
