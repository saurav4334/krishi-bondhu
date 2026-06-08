<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_name',
        'mobile',
        'vehicle_type',
        'vehicle_number',
        'district',
        'service_area',
        'rate_per_km',
        'availability_status',
    ];

    protected $casts = [
        'rate_per_km' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TransportBooking::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }
}
