<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'crop_name',
        'quantity',
        'price',
        'location',
        'mobile',
        'description',
        'image',
        'status',
        'featured',
        'approved',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CropPostImage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Visible to the public: active + admin-approved. */
    public function scopePublished($query)
    {
        return $query->where('status', 'active')->where('approved', true);
    }
}
