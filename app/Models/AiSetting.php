<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = ['status', 'daily_limit', 'guest_limit'];

    protected $casts = [
        'status' => 'boolean',
        'daily_limit' => 'integer',
        'guest_limit' => 'integer',
    ];

    /** The single settings row (created with sensible defaults on first access). */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'status' => true,
            'daily_limit' => 10,
            'guest_limit' => 3,
        ]);
    }
}
