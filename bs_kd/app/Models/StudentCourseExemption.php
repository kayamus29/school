<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourseExemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'student_id',
        'course_id',
        'reason',
        'removed_by',
    ];
}
