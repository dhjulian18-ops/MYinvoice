<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model {
    protected $fillable = [
        'invoice_id', 'description', 'qty', 'price', 'tax', 'discount',
    ];

    protected $casts = [
        'price' => 'float',
        'tax' => 'float',
        'discount' => 'float',
    ];

    public function getAmountAttribute(): float {
        return $this->qty * $this->price;
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
}
