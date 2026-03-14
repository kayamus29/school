<?php

namespace App\Models;

use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AssignedTeacher extends Model
{
    use HasFactory, LogsActivity;

    public const ROLE_SUBJECT_TEACHER = 'subject_teacher';
    public const ROLE_SECTION_TEACHER = 'section_teacher';
    public const ROLE_CLASS_SUPERVISOR = 'class_supervisor';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'teacher_id',
        'semester_id',
        'class_id',
        'section_id',
        'course_id',
        'assignment_role',
        'session_id',
    ];

    protected $appends = ['effective_assignment_role'];

    /**
     * Get the teacher.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the schoolClass.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section.
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function getEffectiveAssignmentRoleAttribute(): string
    {
        if ($this->assignment_role) {
            return $this->assignment_role;
        }

        return $this->course_id
            ? self::ROLE_SUBJECT_TEACHER
            : self::ROLE_SECTION_TEACHER;
    }

    public function scopeSectionTeachers($query)
    {
        return $query->whereNull('course_id')
            ->where(function ($inner) {
                $inner->whereNull('assignment_role')
                    ->orWhere('assignment_role', self::ROLE_SECTION_TEACHER);
            });
    }

    public function scopeSectionLeadership($query)
    {
        return $query->whereNull('course_id')
            ->where(function ($inner) {
                $inner->whereNull('assignment_role')
                    ->orWhereIn('assignment_role', [
                        self::ROLE_SECTION_TEACHER,
                        self::ROLE_CLASS_SUPERVISOR,
                    ]);
            });
    }

    public function scopeForSectionAccess($query, ?int $sectionId)
    {
        return $query->where(function ($inner) use ($sectionId) {
            if ($sectionId) {
                $inner->where('section_id', $sectionId)
                    ->orWhereNull('section_id');

                return;
            }

            $inner->whereNull('section_id');
        });
    }
}
