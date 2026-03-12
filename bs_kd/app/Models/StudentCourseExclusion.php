<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StudentCourseExclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'course_id',
        'session_id',
        'removed_by',
        'reason',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function remover()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public static function excludedCourseIdsForStudent(int $studentId, int $sessionId): array
    {
        return static::query()
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function filterCoursesForStudent(Collection $courses, int $studentId, int $sessionId): Collection
    {
        $excludedIds = static::excludedCourseIdsForStudent($studentId, $sessionId);

        if (empty($excludedIds)) {
            return $courses;
        }

        return $courses->reject(fn ($course) => in_array((int) $course->id, $excludedIds, true))->values();
    }

    public static function filterStudentIdsForCourse(Collection $studentIds, int $courseId, int $sessionId): Collection
    {
        if ($studentIds->isEmpty()) {
            return $studentIds;
        }

        $excludedStudentIds = static::query()
            ->where('session_id', $sessionId)
            ->where('course_id', $courseId)
            ->whereIn('student_id', $studentIds->all())
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($excludedStudentIds)) {
            return $studentIds;
        }

        return $studentIds->reject(fn ($studentId) => in_array((int) $studentId, $excludedStudentIds, true))->values();
    }
}
