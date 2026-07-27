<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Client;
use App\Models\Item;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Dashboard Overview Stats ──
    public function dashboard(Request $request)
    {
        $totalUsers = User::count();
        $totalBusinesses = Business::count();
        $totalClients = Client::count();
        $totalItems = Item::count();
        $totalInvoices = Invoice::count();

        // Calculate total revenue from all invoices
        $totalRevenue = Invoice::all()->sum(function ($inv) {
            $subtotal = collect($inv->items ?? [])->sum(function ($it) {
                $qty = $it['quantity'] ?? 1;
                $price = $it['price'] ?? 0;
                return $qty * $price;
            });
            $discount = $inv->discount ?? 0;
            $tax = $inv->tax ?? 0;
            $shipping = $inv->shipping ?? 0;
            return max(0, $subtotal - $discount - $tax + $shipping);
        });

        $recentUsers = User::latest()->take(5)->get();
        $recentInvoices = Invoice::with(['client', 'business.user'])->latest()->take(5)->get();

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
        $invoices = Invoice::with(['client', 'business.user'])->latest()->get();

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
