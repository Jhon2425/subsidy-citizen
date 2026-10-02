<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Merge with your existing Announcement model if it has extra code.
 * The important additions: 'subsidy_id' in $fillable and the subsidy() relation.
 */
class Announcement extends Model
{
    protected $fillable = [
        'title',
        'subsidy_id',
        'message',
        'distribution_date',
        'distribution_location',
        'status',
        'recipients_count',
        'sent_count',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'sent_at'           => 'datetime',
    ];

    public function barangays()
    {
        return $this->hasMany(AnnouncementBarangay::class);
    }

    public function subsidy()
    {
        return $this->belongsTo(Subsidy::class);
    }
}