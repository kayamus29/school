<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Section;
use App\Models\Syllabus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['class_name', 'session_id', 'is_final_grade'];

    /**
     * Get the sections for the class.
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id', 'id');
    }

    /**
     * Get the courses for the class.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id', 'id');
    }

    /**
     * Get the grading systems for the class.
     */
    public function gradingSystems()
    {
        return $this->belongsToMany(GradingSystem::class, 'class_grading_system', 'class_id', 'grading_system_id');
    }

    /**
     * Get the syllabi for the class.
     */
    public function syllabi()
    {
        return $this->hasMany(Syllabus::class, 'class_id', 'id');
    }

    /**
     * Get the class fees for the class.
     */
    public function classFees()
    {
        return $this->hasMany(ClassFee::class, 'class_id');
    }

    /**
     * Get the assigned teachers for the class.
     */
    public function assignedTeachers()
    {
        return $this->hasMany(AssignedTeacher::class, 'class_id');
    }
    /**
     * Get fee items for a specific session and term.
     */
    public function getFeeItems($sessionId, $semesterId)
    {
        return $this->classFees()
            ->where('session_id', $sessionId)
            ->where('semester_id', $semesterId)
            ->with('feeHead')
            ->get();
    }
}
