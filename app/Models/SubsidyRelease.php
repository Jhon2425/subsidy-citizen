<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubsidyRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'senior_citizen_id', 'subsidy_id', 'reference_number', 'amount',
        'release_date', 'status', 'released_by', 'remarks',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'release_date' => 'date',
    ];

    public function seniorCitizen(): BelongsTo
    {
        return $this->belongsTo(SeniorCitizen::class);
    }

    public function subsidy(): BelongsTo
    {
        return $this->belongsTo(Subsidy::class);
    }
}