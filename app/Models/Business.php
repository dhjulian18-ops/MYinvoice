<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model {
    protected $fillable = [
        'user_id', 'name', 'phone', 'email',
        'address1', 'address2', 'website',
        'tax_id', 'tax_number', 'license_number',
        'logo_base64', 'is_active',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    public function clients(): HasMany {
        return $this->hasMany(Client::class);
    }
    public function items(): HasMany {
        return $this->hasMany(Item::class);
    }
    public function invoices(): HasMany {
        return $this->hasMany(Invoice::class);
    }
}
