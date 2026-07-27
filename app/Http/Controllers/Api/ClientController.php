<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller {

    private function business(Request $request) {
        $user = $request->user();
        $biz = $user->businesses()
            ->where('is_active', true)->first()
            ?? $user->businesses()->first();

        if (!$biz) {
            $biz = $user->businesses()->create([
                'name' => 'Bisnis ' . ($user->name ?? 'Saya'),
                'is_active' => true,
            ]);
        }
        return $biz;
    }

    // GET /api/clients
    public function index(Request $request) {
        $clients = $this->business($request)->clients()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->get();
        return response()->json($clients);
    }

    // POST /api/clients
    public function store(Request $request) {
        $request->validate(['name' => 'required|string']);
        $client = $this->business($request)->clients()->create($request->only([
            'name', 'phone', 'email', 'address1', 'address2',
            'shipping_address', 'tax_id', 'tax_number', 'license',
        ]));
        return response()->json(['message' => 'Klien ditambahkan', 'data' => $client], 201);
    }

    // GET /api/clients/{id}
    public function show(Request $request, $id) {
        $client = $this->business($request)->clients()->findOrFail($id);
        return response()->json($client);
    }

    // PUT /api/clients/{id}
    public function update(Request $request, $id) {
        $client = $this->business($request)->clients()->findOrFail($id);
        $client->update($request->only([
            'name', 'phone', 'email', 'address1', 'address2',
            'shipping_address', 'tax_id', 'tax_number', 'license',
        ]));
        return response()->json(['message' => 'Klien diupdate', 'data' => $client]);
    }

    // DELETE /api/clients/{id}
    public function destroy(Request $request, $id) {
        $this->business($request)->clients()->findOrFail($id)->delete();
        return response()->json(['message' => 'Klien dihapus']);
    }

    // DELETE /api/clients (hapus semua)
    public function destroyAll(Request $request) {
        $this->business($request)->clients()->delete();
        return response()->json(['message' => 'Semua klien dihapus']);
    }
}
