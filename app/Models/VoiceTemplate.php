<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceTemplate extends Model
{
    protected $fillable = ['type', 'title', 'start_text', 'question_text', 'end_text', 'dtmf_options', 'status'];

    protected $casts = [
        'dtmf_options' => 'array',
        'status' => 'boolean',
    ];

    /** Feature types handled by the voice module. */
    public const TYPES = [
        'weather_alert' => 'স্মার্ট আবহাওয়া সতর্কতা',
        'crop_lead' => 'ফসল বিক্রয় লিড নিশ্চিতকরণ',
        'equipment_rental' => 'যন্ত্রপাতি ভাড়া নিশ্চিতকরণ',
        'labor_match' => 'শ্রমিক সেবা ম্যাচিং',
        'govt_circular' => 'সরকারি বিজ্ঞপ্তি ভয়েস অ্যালার্ট',
    ];

    public static function forType(string $type): ?self
    {
        return static::where('type', $type)->where('status', true)->first();
    }
}
