<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'district',
        'area',
        'skill_type',
        'daily_wage',
        'experience',
        'availability_status',
        'image',
    ];

    protected $casts = [
        'daily_wage' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }
}
