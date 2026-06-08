<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_name',
        'district',
        'unit',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
