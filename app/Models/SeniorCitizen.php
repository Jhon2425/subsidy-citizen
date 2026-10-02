<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeniorCitizen extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'list_name',
        'birth_date',
        'gender',
        'address',
        'region',
        'region_code',
        'province',
        'province_code',
        'municipality',
        'municipality_code',
        'barangay',
        'barangay_code',
        'contact_number',
        'is_active',
        'deceased_at',
    ];

    protected $casts = [
        'birth_date'  => 'date',
        'is_active'   => 'boolean',
        'deceased_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Subsidies this senior citizen is enlisted in.
     */
    public function subsidies()
    {
        return $this->belongsToMany(Subsidy::class, 'subsidy_senior_citizen')
            ->withTimestamps();
    }

    /**
     * Subsidies this senior is enlisted in (an announcement was posted for
     * them) that are active and not yet released. Drives the Subsidies page.
     */
    public function pendingSubsidies()
    {
        return $this->belongsToMany(Subsidy::class, 'subsidy_senior_citizen')
            ->where('subsidies.is_active', true)
            ->whereHas('announcements')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('subsidy_releases')
                    ->whereColumn('subsidy_releases.subsidy_id', 'subsidies.id')
                    ->whereColumn('subsidy_releases.senior_citizen_id', 'subsidy_senior_citizen.senior_citizen_id')
                    ->where('subsidy_releases.status', 'released');
            });
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->list_name)) ?: [];

        $first = $parts[0][0] ?? '';
        $last  = $parts[count($parts) - 1][0] ?? '';

        return strtoupper($first . $last);
    }

    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? 0;
    }

    /**
     * Scope: only active (non-deceased) records, the default for the master list.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: seniors eligible for subsidies and SMS
     * (promoted from an approved application, active, not deceased).
     */
    public function scopeEligible($query)
    {
        return $query->where('is_active', true)
                     ->whereNull('deceased_at');
    }

    public function scopeBarangay($query, ?string $barangay)
    {
        return $barangay ? $query->where('barangay', $barangay) : $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('list_name', 'like', "%{$term}%") : $query;
    }
}