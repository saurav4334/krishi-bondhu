<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborJobPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'job_type',
        'location',
        'worker_needed',
        'wage',
        'duration',
        'contact_number',
        'status',
    ];

    protected $casts = [
        'wage' => 'decimal:2',
        'worker_needed' => 'integer',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
