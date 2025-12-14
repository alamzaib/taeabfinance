<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'metadata',
        'paid_at',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'payment_link',
        'stripe_customer_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }
}
