<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReportComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'semester_id',
        'session_id',
        'teacher_comment',
        'principal_comment'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
