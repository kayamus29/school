<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Http\Requests\TeacherAssignRequest;
use App\Repositories\AssignedTeacherRepository;

class AssignedTeacherController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $semesterRepository;

    /**
     * Create a new Controller instance
     * 
     * @param SchoolSessionInterface $schoolSessionRepository
     * @return void
     */
    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SemesterInterface $semesterRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->semesterRepository = $semesterRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function getTeacherCourses(Request $request)
    {
        $teacher_id = $request->query('teacher_id');
        $semester_id = $request->query('semester_id');

        if ($teacher_id == null) {
            abort(404);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $semesters = $this->semesterRepository->getAll($current_school_session_id);

        $assignedTeacherRepository = new AssignedTeacherRepository();

        if ($semester_id == null) {
            $courses = [];
        } else {
            $courses = $assignedTeacherRepository->getTeacherCourses($current_school_session_id, $teacher_id, $semester_id);
        }

        $data = [
            'courses' => $courses,
            'semesters' => $semesters,
            'selected_semester_id' => $semester_id,
        ];

        return view('courses.teacher', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherAssignRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TeacherAssignRequest $request)
    {
        try {
            $assignedTeacherRepository = new AssignedTeacherRepository();
            $assignedTeacherRepository->assign($request->validated());

            return back()->with('status', 'Assigning teacher was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:school_sessions,id',
            'section_teachers' => 'nullable|array',
            'section_teachers.*' => 'nullable|exists:users,id',
            'course_teachers' => 'nullable|array',
            'course_teachers.*' => 'nullable|array',
        ]);

        try {
            $current_school_session_id = $this->getSchoolCurrentSession();
            $semester_id = $this->semesterRepository->getAll($current_school_session_id)->first()->id ?? 1;

            // 1. Assign Section Teachers (Course NULL)
            if ($request->has('section_teachers')) {
                foreach ($request->section_teachers as $section_id => $teacher_id) {
                    if ($teacher_id) {
                        \App\Models\AssignedTeacher::updateOrCreate(
                            [
                                'class_id' => $request->class_id,
                                'session_id' => $request->session_id,
                                'section_id' => $section_id,
                                'course_id' => null,
                                'semester_id' => $semester_id
                            ],
                            [
                                'teacher_id' => $teacher_id
                            ]
                        );
                    } else {
                        // Remove assignment if empty? (Optional, but usually expected)
                        \App\Models\AssignedTeacher::where([
                            'class_id' => $request->class_id,
                            'session_id' => $request->session_id,
                            'section_id' => $section_id,
                            'course_id' => null,
                            'semester_id' => $semester_id
                        ])->delete();
                    }
                }
            }

            // 2. Assign Course Teachers per Section
            if ($request->has('course_teachers')) {
                foreach ($request->course_teachers as $section_id => $courses) {
                    foreach ($courses as $course_id => $teacher_id) {
                        if ($teacher_id) {
                            \App\Models\AssignedTeacher::updateOrCreate(
                                [
                                    'class_id' => $request->class_id,
                                    'session_id' => $request->session_id,
                                    'section_id' => $section_id,
                                    'course_id' => $course_id,
                                    'semester_id' => $semester_id
                                ],
                                [
                                    'teacher_id' => $teacher_id
                                ]
                            );
                        } else {
                            \App\Models\AssignedTeacher::where([
                                'class_id' => $request->class_id,
                                'session_id' => $request->session_id,
                                'section_id' => $section_id,
                                'course_id' => $course_id,
                                'semester_id' => $semester_id
                            ])->delete();
                        }
                    }
                }
            }

            return back()->with('status', 'Teachers assigned successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
