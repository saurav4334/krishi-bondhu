<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticle extends Model
{
    protected $fillable = [
        'category_id', 'title', 'question', 'keywords', 'answer',
        'source_name', 'source_url', 'source_type',
        'status', 'views_count', 'helpful_count',
    ];

    /** Label shown as the chatbot answer source. */
    public function getSourceLabelAttribute(): string
    {
        return ($this->source_name && $this->source_type !== 'conversational')
            ? $this->source_name
            : 'কৃষি জ্ঞানভান্ডার';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
