<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementBarangay extends Model
{
    protected $fillable = ['announcement_id', 'barangay_code', 'barangay_name'];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}