<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'brand',
        'model',
        'price',
        'stock_quantity',
        'unit',
        'condition',
        'location',
        'upazila',
        'mobile',
        'whatsapp',
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

    public const CONDITIONS = [
        'new' => 'নতুন',
        'used' => 'ব্যবহৃত',
    ];

    public function getConditionLabelAttribute(): ?string
    {
        return $this->condition ? (self::CONDITIONS[$this->condition] ?? $this->condition) : null;
    }

    /**
     * Resolved thumbnail URL — the stored public-disk image when it exists,
     * otherwise the placeholder. Works on localhost and shared cPanel hosting.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        return asset('images/no-product.png');
    }

    /** WhatsApp falls back to the primary mobile when not provided. */
    public function getWhatsappNumberAttribute(): string
    {
        return $this->whatsapp ?: $this->mobile;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EquipmentProductImage::class);
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
