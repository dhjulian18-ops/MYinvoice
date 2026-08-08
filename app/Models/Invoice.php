<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {
    protected $fillable = [
        'business_id', 'client_id', 'invoice_number', 'invoice_title',
        'customer_name', 'customer_phone', 'date', 'due_date', 'po_number',
        'global_discount', 'global_tax', 'shipping',
        'payment_status', 'status', 'payments', 'payment_method',
        'terms', 'signature', 'currency', 'template', 'notes',
    ];

    protected $casts = [
        'payments' => 'array',
        'global_discount' => 'float',
        'global_tax' => 'float',
        'shipping' => 'float',
    ];

    protected $appends = ['total'];

    public function business() {
        return $this->belongsTo(Business::class);
    }
    public function client() {
        return $this->belongsTo(Client::class);
    }
    public function invoiceItems() {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getSubtotalAttribute(): float {
        return $this->invoiceItems->sum(fn($i) => $i->qty * $i->price);
    }
    public function getTotalAttribute(): float {
        return $this->subtotal - $this->global_discount + $this->global_tax + $this->shipping;
    }
}
