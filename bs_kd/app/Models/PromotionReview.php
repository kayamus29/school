<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'session_id',
        'class_id',
        'section_id',
        'calculated_average',
        'calculated_status',
        'final_status',
        'is_overridden',
        'override_comment',
        'reviewer_id',
        'reviewed_at',
        'is_finalized'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'is_overridden' => 'boolean',
        'is_finalized' => 'boolean',
        'calculated_average' => 'float'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
