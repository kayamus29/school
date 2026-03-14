<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\AssignedTeacher;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\CourseInterface;
use App\Http\Requests\CourseStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    use SchoolSession;
    protected $schoolCourseRepository;
    protected $schoolSessionRepository;
    protected $promotionRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        CourseInterface $schoolCourseRepository,
        PromotionRepository $promotionRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolCourseRepository = $schoolCourseRepository;
        $this->promotionRepository = $promotionRepository;
    }

    // Helper: Scoped Class Query (Returns Builder)
    private function getAccessibleClasses()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return SchoolClass::query();
        }

        if ($user->hasRole('Teacher')) {
            $current_school_session_id = $this->getSchoolCurrentSession();
            return SchoolClass::whereIn('id', function ($query) use ($user, $current_school_session_id) {
                $query->select('class_id')
                    ->from('assigned_teachers')
                    ->where('teacher_id', $user->id)
                    ->where('session_id', $current_school_session_id);
            });
        }

        // Default Deny
        return SchoolClass::whereRaw('1 = 0');
    }

    public function index()
    {
        // View Permission
        if (!Auth::user()->can('view assigned classes') && !Auth::user()->hasRole('Admin')) {
            abort(403);
        }

        // Return scoped list (e.g. for a dropdown or view)
        // Legacy controller didn't implement Index, but we'll add it for completeness if needed.
        // For now, adhering to legacy structure which was empty, but adding security if used.
    }

    public function create()
    {
        if (!Auth::user()->can('manage courses') && !Auth::user()->hasRole('Admin')) {
            abort(403);
        }
        // Legacy was empty...
    }

    public function store(CourseStoreRequest $request)
    {
        // Permission Check
        if (!Auth::user()->can('manage courses') && !Auth::user()->hasRole('Admin')) {
            // Teachers generally don't create courses, but if requested:
            abort(403);
        }

        // Secure Validation
        $request->validate([
            'class_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (Auth::user()->hasRole('Admin'))
                        return;

                    $current_school_session_id = \App\Models\SchoolSession::latest()->first()->id;
                    $exists = AssignedTeacher::where('teacher_id', Auth::id())
                        ->where('class_id', $value)
                        ->where('session_id', $current_school_session_id)
                        ->exists();

                    if (!$exists)
                        $fail('Unauthorized class.');
                }
            ]
        ]);

        try {
            $this->schoolCourseRepository->create($request->validated());
            return back()->with('status', 'Course creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getStudentCourses($student_id)
    {
        // Student Self-Check or Parent Check
        if (Auth::user()->hasRole('Student')) {
            if (Auth::id() != $student_id)
                abort(403);
        }
        if (Auth::user()->hasRole('Parent')) {
            // Need child check logic here, but for now simple permission guard
            if (!Auth::user()->can('view child records'))
                abort(403);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();
        $class_info = $this->promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);

        // Scope Check? accessing courses of a class. Public info for that class effectively.
        // But let's be safe.

        $courses = $this->schoolCourseRepository->getByClassId($class_info->class_id);

        $data = [
            'class_info' => $class_info,
            'courses' => $courses,
        ];
        return view('courses.student', $data);
    }

    public function edit($course_id)
    {
        if (!Auth::user()->can('manage courses') && !Auth::user()->hasRole('Admin')) {
            abort(403);
        }

        // Ownership Check for Edit
        $course = $this->schoolCourseRepository->findById($course_id);
        if (Auth::user()->hasRole('Teacher')) {
            $isAllowed = $this->getAccessibleClasses()->where('id', $course->class_id)->exists();
            if (!$isAllowed)
                abort(403, 'Unauthorized.');
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'course' => $course,
            'course_id' => $course_id,
        ];

        return view('courses.edit', $data);
    }

    public function update(Request $request)
    {
        if (!Auth::user()->can('manage courses') && !Auth::user()->hasRole('Admin')) {
            abort(403);
        }

        // Validation with Scope
        $request->validate([
            'class_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (Auth::user()->hasRole('Admin'))
                        return;

                    $current_school_session_id = \App\Models\SchoolSession::latest()->first()->id;
                    $exists = AssignedTeacher::where('teacher_id', Auth::id())
                        ->where('class_id', $value)
                        ->where('session_id', $current_school_session_id)
                        ->exists();

                    if (!$exists)
                        $fail('Unauthorized class.');
                }
            ]
        ]);

        try {
            $this->schoolCourseRepository->update($request);

            return back()->with('status', 'Course update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function destroy(Course $course)
    {
        //
    }
}
