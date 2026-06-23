<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceSetting extends Model
{
    protected $fillable = ['api_base_url', 'api_token', 'sender', 'default_voice', 'language_code', 'status'];

    protected $casts = [
        'api_token' => 'encrypted', // never stored or exposed in plaintext
        'status' => 'boolean',
    ];

    /** The single active settings row (created on first access, seeded from .env). */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'api_base_url' => config('services.protiddhoni.url'),
            'api_token' => config('services.protiddhoni.token') ?: null,
            'sender' => config('services.protiddhoni.sender'),
            'default_voice' => config('services.protiddhoni.voice', 'female'),
            'language_code' => config('services.protiddhoni.language', 'bn'),
            'status' => false,
        ]);
    }

    public function isEnabled(): bool
    {
        return $this->status && ! empty($this->api_token) && ! empty($this->sender) && ! empty($this->api_base_url);
    }
}
