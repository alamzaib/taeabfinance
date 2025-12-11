<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'currency',
        'period',
        'features',
        'popular',
        'active',
    ];

    protected $casts = [
        'features' => 'array',
        'popular' => 'boolean',
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
