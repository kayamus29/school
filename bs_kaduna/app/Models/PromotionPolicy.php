<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'session_id',
        'calculation_method',
        'passing_threshold',
        'mandatory_course_ids',
        'probation_logic'
    ];

    protected $casts = [
        'mandatory_course_ids' => 'array',
        'passing_threshold' => 'float'
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
