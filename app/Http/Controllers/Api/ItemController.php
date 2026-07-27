<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller {

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

    // GET /api/items
    public function index(Request $request) {
        $items = $this->business($request)->items()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->get();
        return response()->json($items);
    }

    // POST /api/items
    public function store(Request $request) {
        $request->validate(['name' => 'required|string', 'price' => 'required|numeric']);
        $item = $this->business($request)->items()->create($request->only([
            'name', 'price', 'quantity', 'unit',
            'discount_type', 'discount', 'tax', 'description',
        ]));
        return response()->json(['message' => 'Item ditambahkan', 'data' => $item], 201);
    }

    // GET /api/items/{id}
    public function show(Request $request, $id) {
        return response()->json($this->business($request)->items()->findOrFail($id));
    }

    // PUT /api/items/{id}
    public function update(Request $request, $id) {
        $item = $this->business($request)->items()->findOrFail($id);
        $item->update($request->only([
            'name', 'price', 'quantity', 'unit',
            'discount_type', 'discount', 'tax', 'description',
        ]));
        return response()->json(['message' => 'Item diupdate', 'data' => $item]);
    }

    // DELETE /api/items/{id}
    public function destroy(Request $request, $id) {
        $this->business($request)->items()->findOrFail($id)->delete();
        return response()->json(['message' => 'Item dihapus']);
    }

    // DELETE /api/items (hapus semua)
    public function destroyAll(Request $request) {
        $this->business($request)->items()->delete();
        return response()->json(['message' => 'Semua item dihapus']);
    }
}
