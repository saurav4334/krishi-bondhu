<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceCallbackRequest extends Model
{
    protected $fillable = ['user_id', 'feature_type', 'related_id', 'phone', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
