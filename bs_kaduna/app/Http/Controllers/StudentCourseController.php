<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Models\AssignedTeacher;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentCourseExclusion;

class StudentCourseController extends Controller
{
    use SchoolSession;

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $promotion = Promotion::where('student_id', $request->student_id)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        $this->authorizeSectionTeacher($promotion->class_id, $promotion->section_id);

        $course = Course::where('id', $request->course_id)
            ->where('class_id', $promotion->class_id)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        StudentCourseExclusion::firstOrCreate(
            [
                'student_id' => $promotion->student_id,
                'class_id' => $promotion->class_id,
                'section_id' => $promotion->section_id,
                'course_id' => $course->id,
                'session_id' => $sessionId,
            ],
            [
                'removed_by' => Auth::id(),
                'reason' => $request->reason,
            ]
        );

        return back()->with('status', 'Subject removed for this student.');
    }

    public function destroy(Request $request, StudentCourseExclusion $studentCourseExclusion)
    {
        $this->authorizeSectionTeacher($studentCourseExclusion->class_id, $studentCourseExclusion->section_id);
        $studentCourseExclusion->delete();

        return back()->with('status', 'Subject restored for this student.');
    }

    private function authorizeSectionTeacher(int $classId, int $sectionId): void
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return;
        }

        $sessionId = $this->getSchoolCurrentSession();
        $isSectionTeacher = AssignedTeacher::query()
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->sectionTeachers()
            ->exists();

        if (!$isSectionTeacher) {
            abort(403, 'Only the assigned section teacher can manage student subjects.');
        }
    }
}
