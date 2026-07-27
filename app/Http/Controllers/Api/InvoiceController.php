<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceController extends Controller {

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

    // GET /api/invoices
    public function index(Request $request) {
        $invoices = $this->business($request)->invoices()
            ->with('invoiceItems')
            ->when($request->search, fn($q) =>
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($inv) => $this->formatInvoice($inv));

        return response()->json($invoices);
    }

    // POST /api/invoices
    public function store(Request $request) {
        $request->validate([
            'invoice_number' => 'required|string',
            'date'           => 'required|string',
            'due_date'       => 'required|string',
        ]);

        $invoice = $this->business($request)->invoices()->create($request->only([
            'client_id', 'invoice_number', 'invoice_title',
            'customer_name', 'customer_phone', 'date', 'due_date', 'po_number',
            'global_discount', 'global_tax', 'shipping',
            'payment_status', 'status', 'payments', 'payment_method',
            'terms', 'signature', 'currency', 'template', 'notes',
        ]));

        // Simpan items
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                $invoice->invoiceItems()->create([
                    'description' => $item['description'] ?? '-',
                    'qty'         => $item['qty'] ?? 1,
                    'price'       => $item['price'] ?? 0,
                    'tax'         => $item['tax'] ?? 0,
                    'discount'    => $item['discount'] ?? 0,
                ]);
            }
        }

        $invoice->load('invoiceItems');
        return response()->json([
            'message' => 'Invoice dibuat',
            'data'    => $this->formatInvoice($invoice),
        ], 201);
    }

    // GET /api/invoices/{id}
    public function show(Request $request, $id) {
        $invoice = $this->business($request)->invoices()
            ->with('invoiceItems')->findOrFail($id);
        return response()->json($this->formatInvoice($invoice));
    }

    // PUT /api/invoices/{id}
    public function update(Request $request, $id) {
        $invoice = $this->business($request)->invoices()->findOrFail($id);

        $invoice->update($request->only([
            'client_id', 'invoice_number', 'invoice_title',
            'customer_name', 'customer_phone', 'date', 'due_date', 'po_number',
            'global_discount', 'global_tax', 'shipping',
            'payment_status', 'status', 'payments', 'payment_method',
            'terms', 'signature', 'currency', 'template', 'notes',
        ]));

        // Update items — hapus lama, buat baru
        if ($request->has('items')) {
            $invoice->invoiceItems()->delete();
            foreach ($request->items as $item) {
                $invoice->invoiceItems()->create([
                    'description' => $item['description'] ?? '-',
                    'qty'         => $item['qty'] ?? 1,
                    'price'       => $item['price'] ?? 0,
                    'tax'         => $item['tax'] ?? 0,
                    'discount'    => $item['discount'] ?? 0,
                ]);
            }
        }

        $invoice->load('invoiceItems');
        return response()->json([
            'message' => 'Invoice diupdate',
            'data'    => $this->formatInvoice($invoice),
        ]);
    }

    // DELETE /api/invoices/{id}
    public function destroy(Request $request, $id) {
        $this->business($request)->invoices()->findOrFail($id)->delete();
        return response()->json(['message' => 'Invoice dihapus']);
    }

    // DELETE /api/invoices (hapus semua)
    public function destroyAll(Request $request) {
        $this->business($request)->invoices()->delete();
        return response()->json(['message' => 'Semua invoice dihapus']);
    }

    // Format response invoice
    private function formatInvoice(Invoice $inv): array {
        $items = $inv->invoiceItems->map(fn($i) => [
            'id'          => (string) $i->id,
            'itemId'      => '',
            'description' => $i->description,
            'qty'         => $i->qty,
            'price'       => (float) $i->price,
            'tax'         => (float) $i->tax,
            'discount'    => (float) $i->discount,
        ])->toArray();

        return [
            'id'             => (string) $inv->id,
            'invoiceNumber'  => $inv->invoice_number,
            'invoiceTitle'   => $inv->invoice_title ?? 'INVOICE',
            'clientId'       => (string) ($inv->client_id ?? ''),
            'customerName'   => $inv->customer_name ?? '',
            'customerPhone'  => $inv->customer_phone ?? '',
            'companyName'    => $inv->business->name ?? '',
            'companyAddress' => $inv->business->address1 ?? '',
            'companyPhone'   => $inv->business->phone ?? '',
            'date'           => $inv->date,
            'dueDate'        => $inv->due_date,
            'poNumber'       => $inv->po_number,
            'items'          => $items,
            'paymentStatus'  => $inv->payment_status,
            'status'         => $inv->status,
            'globalDiscount' => (float) $inv->global_discount,
            'globalTax'      => (float) $inv->global_tax,
            'shipping'       => (float) $inv->shipping,
            'payments'       => $inv->payments ?? [],
            'paymentMethod'  => $inv->payment_method ?? '',
            'terms'          => $inv->terms,
            'signature'      => $inv->signature,
            'currency'       => $inv->currency ?? 'IDR Rp',
            'template'       => $inv->template ?? 'simple',
            'notes'          => $inv->notes,
        ];
    }
}
