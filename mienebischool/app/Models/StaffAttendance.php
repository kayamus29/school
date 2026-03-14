<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'check_in_at',
        'check_out_at',
        'check_in_lat',
        'check_in_long',
        'check_out_lat',
        'check_out_long',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
