<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transport_provider_id',
        'pickup_location',
        'delivery_location',
        'crop_type',
        'quantity',
        'booking_date',
        'contact_number',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class, 'transport_provider_id');
    }
}
