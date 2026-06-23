<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceCallLog extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'feature_type', 'related_id', 'request_id',
        'payload', 'api_response', 'dtmf_key', 'call_status', 'retry_count',
    ];

    public const MAX_RETRIES = 3;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Calls awaiting dispatch or eligible for retry (cron picks these up). */
    public function scopeDispatchable($query)
    {
        return $query->where(function ($q) {
            $q->where('call_status', 'queued')
                ->orWhere(fn ($r) => $r->where('call_status', 'failed')->where('retry_count', '<', self::MAX_RETRIES));
        });
    }
}
