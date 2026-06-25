<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatLog extends Model
{
    protected $fillable = [
        'user_id', 'ip', 'question', 'answer', 'provider', 'model', 'tokens_used', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Today's Gemini requests for a user (or guest IP). This is the ONLY counter
     * that the daily rate limit uses — Knowledge Base answers never count.
     */
    public static function geminiUsedToday(?int $userId, ?string $ip): int
    {
        return static::query()
            ->whereDate('created_at', today())
            ->where('provider', 'gemini')
            ->when($userId, fn ($q) => $q->where('user_id', $userId), fn ($q) => $q->where('ip', $ip))
            ->count();
    }

    /** Site-wide answer breakdown for today (for the admin usage card). */
    public static function todayCounts(): array
    {
        $today = static::whereDate('created_at', today());
        $gemini = (clone $today)->where('provider', 'gemini')->count();
        $kb = (clone $today)->where('provider', 'knowledge_base')->count();
        $total = (clone $today)->count();

        return [
            'gemini' => $gemini,
            'kb' => $kb,
            'total' => $total,
            'gemini_pct' => $total > 0 ? round($gemini / $total * 100) : 0,
            'kb_pct' => $total > 0 ? round($kb / $total * 100) : 0,
        ];
    }
}
