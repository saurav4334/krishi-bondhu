<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentProductImage extends Model
{
    protected $fillable = ['equipment_product_id', 'image'];

    /** Resolved gallery image URL (handles storage/public/full-URL paths + placeholder). */
    public function getUrlAttribute(): string
    {
        return \App\Support\Media::url($this->image);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(EquipmentProduct::class, 'equipment_product_id');
    }
}
