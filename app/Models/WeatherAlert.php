<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherAlert extends Model
{
    protected $fillable = ['district', 'alert_type', 'title', 'description', 'severity', 'alert_date'];

    protected $casts = [
        'alert_date' => 'date',
    ];

    public const TYPES = [
        'heavy_rain'   => 'ভারী বৃষ্টি সতর্কতা',
        'flood'        => 'বন্যা সতর্কতা',
        'heat_wave'    => 'তাপপ্রবাহ সতর্কতা',
        'thunderstorm' => 'বজ্রঝড় সতর্কতা',
    ];

    public const SEVERITIES = [
        'low'      => 'সামান্য',
        'moderate' => 'মাঝারি',
        'high'     => 'বেশি',
        'severe'   => 'অতি তীব্র',
    ];

    public const TYPE_ICONS = [
        'heavy_rain'   => '🌧️',
        'flood'        => '🌊',
        'heat_wave'    => '🌡️',
        'thunderstorm' => '⛈️',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->alert_type] ?? $this->alert_type;
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::SEVERITIES[$this->severity] ?? $this->severity;
    }

    public function getIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->alert_type] ?? '⚠️';
    }

    public function scopeActiveFor($query, string $district)
    {
        return $query->where('district', $district)
            ->whereDate('alert_date', '>=', now()->toDateString());
    }
}
