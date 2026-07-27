<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model {
    protected $fillable = [
        'business_id', 'name', 'phone', 'email',
        'address1', 'address2', 'shipping_address',
        'tax_id', 'tax_number', 'license',
    ];

    public function business() {
        return $this->belongsTo(Business::class);
    }
    public function invoices() {
        return $this->hasMany(Invoice::class);
    }
}
