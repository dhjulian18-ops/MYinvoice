<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional default.
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, ?string $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key-value associative array.
     */
    public static function getAllKeyValue(): array
    {
        $defaults = [
            'support_name'     => 'Super Admin',
            'support_subtitle' => 'Online Support',
            'app_name'         => 'MyInvoice',
            'contact_email'    => 'support@myinvoice.com',
        ];

        $settings = static::pluck('value', 'key')->all();

        return array_merge($defaults, array_filter($settings, function ($v) {
            return $v !== null && $v !== '';
        }));
    }
}
