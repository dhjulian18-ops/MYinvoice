<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {
    protected $fillable = [
        'business_id', 'name', 'price', 'quantity',
        'unit', 'discount_type', 'discount', 'tax', 'description',
    ];

    public function business() {
        return $this->belongsTo(Business::class);
    }
}
