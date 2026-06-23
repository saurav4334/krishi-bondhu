<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentCategory extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'icon', 'status', 'sort_order'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(EquipmentProduct::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Top-level (main) categories only. */
    public function scopeMains($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isMain(): bool
    {
        return $this->parent_id === null;
    }
}
