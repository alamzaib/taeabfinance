<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get config value by key
     */
    public static function getValue($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set config value
     */
    public static function setValue($key, $value, $description = null)
    {
        $config = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
        return $config;
    }
}
