<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $fillable = ['api_key', 'sender_id', 'sms_type', 'label', 'status'];

    protected $casts = [
        'api_key' => 'encrypted', // never stored or exposed in plaintext
        'status' => 'boolean',
    ];

    /** The single active settings row (created on first access). */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'sms_type' => 'unicode',
            'label' => 'transactional',
            'status' => false,
        ]);
    }

    public function isEnabled(): bool
    {
        return $this->status && ! empty($this->api_key) && ! empty($this->sender_id);
    }
}
