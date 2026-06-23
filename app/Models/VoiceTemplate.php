<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceTemplate extends Model
{
    protected $fillable = ['type', 'title', 'start_text', 'question_text', 'end_text', 'voice_type', 'language_code', 'dtmf_options', 'status'];

    protected $casts = [
        'dtmf_options' => 'array',
        'status' => 'boolean',
    ];

    /** Feature types handled by the voice module. */
    public const TYPES = [
        'weather_alert' => 'স্মার্ট আবহাওয়া সতর্কতা',
        'crop_lead_confirmation' => 'ফসল বিক্রয় ক্রেতা আগ্রহ নিশ্চিতকরণ',
        'equipment_rental_confirmation' => 'কৃষি সরঞ্জাম বুকিং নিশ্চিতকরণ',
        'labor_match' => 'কৃষি শ্রমিক কাজের ম্যাচিং',
        'government_circular' => 'সরকারি কৃষি বিজ্ঞপ্তি ভয়েস অ্যালার্ট',
        'expert_callback' => 'কৃষি বিশেষজ্ঞ কলব্যাক অনুরোধ',
        'market_price_alert' => 'বাজার দর ভয়েস অ্যালার্ট',
        'equipment_inquiry' => 'কৃষি সরঞ্জাম ক্রেতা আগ্রহ',
    ];

    /** The active template used when sending a call for this feature. */
    public static function forType(string $type): ?self
    {
        return static::where('type', $type)->where('status', true)->orderBy('id')->first();
    }
}
