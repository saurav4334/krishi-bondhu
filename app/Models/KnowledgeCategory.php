<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'status', 'sort_order'];

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
