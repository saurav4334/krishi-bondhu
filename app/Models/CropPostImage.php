<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropPostImage extends Model
{
    protected $fillable = ['crop_post_id', 'image'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CropPost::class, 'crop_post_id');
    }
}
