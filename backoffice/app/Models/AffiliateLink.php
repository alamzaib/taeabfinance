<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'affiliate_code',
        'affiliate_link',
        'clicks',
        'signups',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate affiliate code for a user
     */
    public static function generateCode($userId)
    {
        // Generate unique code: USER + user_id + random string
        $code = 'TAEAB' . str_pad($userId, 6, '0', STR_PAD_LEFT) . strtoupper(substr(md5(uniqid()), 0, 4));
        
        // Ensure uniqueness
        while (self::where('affiliate_code', $code)->exists()) {
            $code = 'TAEAB' . str_pad($userId, 6, '0', STR_PAD_LEFT) . strtoupper(substr(md5(uniqid()), 0, 4));
        }
        
        return $code;
    }

    /**
     * Generate affiliate link
     */
    public static function generateLink($code)
    {
        $baseUrl = config('app.frontend_url', 'http://localhost:3000');
        return $baseUrl . '/register?ref=' . $code;
    }
}
