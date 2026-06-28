<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentProductImage extends Model
{
    protected $fillable = ['equipment_product_id', 'image'];

    /** Resolved gallery image URL (public-disk file when present, else placeholder). */
    public function getUrlAttribute(): string
    {
        if ($this->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        return asset('images/no-product.png');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(EquipmentProduct::class, 'equipment_product_id');
    }
}
