<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpToken extends Model {
    protected $fillable = ['phone', 'otp', 'type', 'used', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function isExpired(): bool {
        return now()->isAfter($this->expires_at);
    }

    public function isValid(): bool {
        return !$this->used && !$this->isExpired();
    }
}
