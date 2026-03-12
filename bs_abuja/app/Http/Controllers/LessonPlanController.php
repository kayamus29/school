<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LessonPlan;
use App\Models\Semester;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Models\AssignedTeacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\LessonPlanStoreRequest;

class LessonPlanController extends Controller
{
    use SchoolSession;

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
            abort(403);
        }

        $sessionId = $this->getSchoolCurrentSession();
        $query = LessonPlan::with(['teacher', 'schoolClass', 'section', 'course', 'semester'])
            ->where('session_id', $sessionId)
            ->latest();

        if ($user->hasRole('Teacher')) {
            $query->where('teacher_id', $user->id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $lessonPlans = $query->get();

        return view('lesson-plans.index', [
            'lessonPlans' => $lessonPlans,
            'isAdminView' => $user->hasRole('Admin'),
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
            abort(403);
        }

        $sessionId = $this->getSchoolCurrentSession();
        $semesters = Semester::where('session_id', $sessionId)->orderBy('id')->get();

        if ($user->hasRole('Teacher')) {
            $assignments = AssignedTeacher::with(['schoolClass', 'section', 'course'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->whereNotNull('course_id')
                ->get();
            $schoolClasses = collect();
        } else {
            $assignments = collect();
            $schoolClasses = SchoolClass::where('session_id', $sessionId)->get();
        }

        return view('lesson-plans.create', [
            'assignments' => $assignments,
            'school_classes' => $schoolClasses,
            'semesters' => $semesters,
            'current_school_session_id' => $sessionId,
            'prefill' => $request->only(['class_id', 'section_id', 'course_id', 'semester_id']),
        ]);
    }

    public function store(LessonPlanStoreRequest $request)
    {
        $user = Auth::user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->hasRole('Teacher')) {
            $assigned = AssignedTeacher::query()
                ->where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('course_id', $request->course_id)
                ->exists();

            if (!$assigned) {
                abort(403, 'You are not assigned to this course.');
            }
        }

        $course = Course::where('id', $request->course_id)
            ->where('class_id', $request->class_id)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        $path = null;
        $originalName = null;
        if ($request->hasFile('file')) {
            $path = Storage::disk('public')->put('lesson-plans', $request->file('file'));
            $originalName = $request->file('file')->getClientOriginalName();
        }

        LessonPlan::create([
            'title' => $request->title,
            'teacher_id' => $user->id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'course_id' => $course->id,
            'semester_id' => $request->semester_id,
            'session_id' => $sessionId,
            'content' => $request->content,
            'file_path' => $path,
            'file_name' => $originalName,
        ]);

        return redirect()->route('lesson-plans.index')->with('status', 'Lesson plan saved successfully.');
    }

    public function show(LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
            abort(403);
        }

        if ($user->hasRole('Teacher') && $lessonPlan->teacher_id !== $user->id) {
            abort(403);
        }

        $lessonPlan->load(['teacher', 'schoolClass', 'section', 'course', 'semester']);

        return view('lesson-plans.show', [
            'lessonPlan' => $lessonPlan,
        ]);
    }
}
