<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crop_name',
        'quantity',
        'price',
        'location',
        'mobile',
        'description',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
