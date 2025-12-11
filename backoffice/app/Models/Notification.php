<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'to_email',
        'from_email',
        'from_name',
        'subject',
        'message',
        'data',
        'status',
        'attempts',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get notifications ready to send (pending and not too many attempts)
     */
    public function scopeReadyToSend($query, $maxAttempts = 5)
    {
        return $query->pending()->where('attempts', '<', $maxAttempts);
    }
}

