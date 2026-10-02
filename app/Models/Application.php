<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'subsidy_id',
        'last_name', 'first_name', 'middle_name', 'name_extension',
        'birth_date', 'gender',
        'address',
        'region', 'region_code',
        'province', 'province_code',
        'municipality', 'municipality_code',
        'barangay', 'barangay_code',
        'contact_number',
        'status', 'reason',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function subsidy(): BelongsTo
    {
        return $this->belongsTo(Subsidy::class);
    }

    /** "Dela Cruz, Juan Santos Jr." */
    protected function fullName(): Attribute
    {
        return Attribute::get(function () {
            $given = trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->name_extension);

            return trim($this->last_name . ', ' . $given, ', ');
        });
    }

    protected function initials(): Attribute
    {
        return Attribute::get(fn () => strtoupper(
            mb_substr($this->first_name, 0, 1) . mb_substr($this->last_name, 0, 1)
        ));
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn () => $this->birth_date?->age);
    }
}