<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = ['user_id', 'mobile', 'message', 'purpose', 'response', 'status', 'recipients'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
