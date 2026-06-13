<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['mobile', 'otp', 'purpose', 'expires_at', 'verified_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')->where('expires_at', '>', now());
    }
}
