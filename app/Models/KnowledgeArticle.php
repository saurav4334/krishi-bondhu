<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticle extends Model
{
    protected $fillable = [
        'category_id', 'title', 'question', 'keywords', 'answer',
        'status', 'views_count', 'helpful_count',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
