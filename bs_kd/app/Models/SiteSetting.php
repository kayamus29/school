<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_logo_path',
        'login_background_path',
        'primary_color',
        'secondary_color',
        'office_lat',
        'office_long',
        'geo_range',
        'late_time',
    ];
}
