<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'disease_name',
        'confidence_score',
        'symptoms',
        'treatment',
        'prevention',
        'ai_result',
        'status',
    ];

    protected $casts = [
        'ai_result' => 'array',
        'confidence_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
