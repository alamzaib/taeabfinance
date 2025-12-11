<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Earning extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'type',
        'description',
        'amount',
        'currency',
        'status',
        'earned_date',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'earned_date' => 'date',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
