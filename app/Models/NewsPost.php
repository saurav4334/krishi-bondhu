<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsPost extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'image',
        'district',
        'is_important',
        'is_demo',
        'views_count',
        'status',
        'published_at',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'is_demo' => 'boolean',
        'published_at' => 'datetime',
    ];

    /** Resolved image URL (storage / public / full-URL + news placeholder). */
    public function getImageUrlAttribute(): string
    {
        return \App\Support\Media::url($this->image, 'images/news/default.jpg');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
