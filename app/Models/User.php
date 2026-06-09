<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'mobile',
        'division',
        'district',
        'upazila',
        'union_name',
        'role',
        'password',
        'daily_scan_count',
        'last_scan_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_scan_date' => 'date',
        'password' => 'hashed',
    ];

    public function scans(): HasMany
    {
        return $this->hasMany(DiseaseScan::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CropPost::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isExpert(): bool
    {
        return $this->role === 'expert';
    }

    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    public function getRoleLabel(): string
    {
        return match($this->role) {
            'admin' => '⚙️ অ্যাডমিন',
            'expert' => '👨‍🔬 বিশেষজ্ঞ',
            default => '🧑‍🌾 কৃষক',
        };
    }

    /**
     * Tells Laravel to use 'mobile' as the username for login
     */
    public function getAuthIdentifierName()
    {
        return 'mobile';
    }
}
