<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'payment_id',
        'commission_amount',
        'commission_type',
        'commission_rate',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
