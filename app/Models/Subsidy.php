<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subsidy extends Model
{
    use HasFactory;

    public const FREQUENCIES = [
        'one_time'  => 'One-time',
        'monthly'   => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly'    => 'Yearly',
    ];

    protected $fillable = ['name', 'description', 'amount', 'frequency', 'is_active'];

    protected $casts = [
        'amount'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function releases(): HasMany
    {
        return $this->hasMany(SubsidyRelease::class);
    }

    /**
     * Senior citizens enlisted in this subsidy (auto-enlisted when an
     * announcement for this subsidy is saved).
     */
    public function seniorCitizens(): BelongsToMany
    {
        return $this->belongsToMany(SeniorCitizen::class, 'subsidy_senior_citizen')
            ->withTimestamps();
    }

    /**
     * Announcements posted for this subsidy.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
}