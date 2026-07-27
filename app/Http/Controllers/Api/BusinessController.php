<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller {

    // GET /api/businesses
    public function index(Request $request) {
        return response()->json(
            $request->user()->businesses()->orderBy('created_at', 'desc')->get()
        );
    }

    // POST /api/businesses
    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);

        $business = $request->user()->businesses()->create($request->only([
            'name', 'phone', 'email', 'address1', 'address2',
            'website', 'tax_id', 'tax_number', 'license_number', 'logo_base64',
        ]));

        return response()->json(['message' => 'Bisnis dibuat', 'data' => $business], 201);
    }

    // GET /api/businesses/{id}
    public function show(Request $request, $id) {
        $business = $request->user()->businesses()->findOrFail($id);
        return response()->json($business);
    }

    // PUT /api/businesses/{id}
    public function update(Request $request, $id) {
        $business = $request->user()->businesses()->findOrFail($id);
        $business->update($request->only([
            'name', 'phone', 'email', 'address1', 'address2',
            'website', 'tax_id', 'tax_number', 'license_number', 'logo_base64',
        ]));
        return response()->json(['message' => 'Bisnis diupdate', 'data' => $business]);
    }

    // DELETE /api/businesses/{id}
    public function destroy(Request $request, $id) {
        $request->user()->businesses()->findOrFail($id)->delete();
        return response()->json(['message' => 'Bisnis dihapus']);
    }

    // POST /api/businesses/{id}/activate
    public function activate(Request $request, $id) {
        // Nonaktifkan semua, aktifkan yang dipilih
        $request->user()->businesses()->update(['is_active' => false]);
        $business = $request->user()->businesses()->findOrFail($id);
        $business->update(['is_active' => true]);
        return response()->json(['message' => 'Bisnis aktif', 'data' => $business]);
    }
}
