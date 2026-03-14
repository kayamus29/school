<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\AcademicSettingInterface;
use App\Http\Requests\AttendanceStoreRequest;
use App\Interfaces\SectionInterface;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseRepository;
use App\Traits\SchoolSession;
use App\Models\AssignedTeacher;
use App\Models\SchoolSession as SchoolSessionModel;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    use SchoolSession;
    protected $academicSettingRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $sectionRepository;
    protected $userRepository;

    public function __construct(
        UserInterface $userRepository,
        AcademicSettingInterface $academicSettingRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $sectionRepository
    ) {
        $this->middleware(['can:view-attendance-pages'])->except(['showStudentAttendance']);

        $this->userRepository = $userRepository;
        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->sectionRepository = $sectionRepository;
    }

    // Helper: Scoped Class Query (Returns Builder)
    private function getAccessibleClasses()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return \App\Models\SchoolClass::query();
        }

        if ($user->hasRole('Teacher')) {
            $current_school_session_id = $this->getSchoolCurrentSession();
            return \App\Models\SchoolClass::whereIn('id', function ($query) use ($user, $current_school_session_id) {
                $query->select('class_id')
                    ->from('assigned_teachers')
                    ->where('teacher_id', $user->id)
                    ->where('session_id', $current_school_session_id);
            });
        }

        return \App\Models\SchoolClass::whereRaw('1 = 0');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('take attendance') && !Auth::user()->can('view attendances')) {
            abort(403);
        }

        $classes = $this->getAccessibleClasses()->get();

        return view('attendance.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->query('class_id') == null) {
            return abort(404);
        }

        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id', 0);
        $course_id = $request->query('course_id');
        $current_school_session_id = $this->getSchoolCurrentSession();

        // Strict Scoping Check
        if (Auth::user()->hasRole('Teacher')) {
            $isAssigned = AssignedTeacher::where('teacher_id', Auth::id())
                ->where('class_id', $class_id)
                ->where('session_id', $current_school_session_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized access to take attendance.');
            }
        }

        try {
            $academic_setting = $this->academicSettingRepository->getAcademicSetting();
            $student_list = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);

            $school_class = $this->schoolClassRepository->findById($class_id);
            $school_section = $this->sectionRepository->findById($section_id);

            $attendanceRepository = new AttendanceRepository();

            if ($academic_setting->attendance_type == 'section') {
                $attendance_count = $attendanceRepository->getSectionAttendance($class_id, $section_id, $current_school_session_id)->count();
            } else {
                $attendance_count = $attendanceRepository->getCourseAttendance($class_id, $course_id, $current_school_session_id)->count();
            }

            $data = [
                'current_school_session_id' => $current_school_session_id,
                'academic_setting' => $academic_setting,
                'student_list' => $student_list,
                'school_class' => $school_class,
                'school_section' => $school_section,
                'attendance_count' => $attendance_count,
                'course_id' => $course_id
            ];

            return view('attendances.take', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\AttendanceStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttendanceStoreRequest $request)
    {
        // Scoping is handled by Request validation or here
        if (Auth::user()->hasRole('Teacher')) {
            $current_school_session_id = $this->getSchoolCurrentSession();
            $section_id = $request->section_id ?? 0;
            $isAssigned = AssignedTeacher::where('teacher_id', Auth::id())
                ->where('class_id', $request->class_id)
                ->where('session_id', $current_school_session_id)
                ->where(function ($q) use ($section_id) {
                    if ($section_id > 0) {
                        $q->where('section_id', $section_id)
                            ->orWhereNull('section_id');
                    }
                })
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Unauthorized to save attendance.');
            }
        }

        try {
            $attendanceRepository = new AttendanceRepository();
            $attendanceRepository->saveAttendance($request->validated());

            return back()->with('status', 'Attendance save was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if ($request->query('class_id') == null) {
            return abort(404);
        }

        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');
        $current_school_session_id = $this->getSchoolCurrentSession();

        // Scoping Check
        if (Auth::user()->hasRole('Teacher')) {
            $isAssigned = AssignedTeacher::where('teacher_id', Auth::id())
                ->where('class_id', $class_id)
                ->where('session_id', $current_school_session_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized access to view attendance.');
            }
        }

        $attendanceRepository = new AttendanceRepository();

        try {
            $academic_setting = $this->academicSettingRepository->getAcademicSetting();
            if ($academic_setting->attendance_type == 'section') {
                $attendances = $attendanceRepository->getSectionAttendance($class_id, $section_id, $current_school_session_id);
            } else {
                $attendances = $attendanceRepository->getCourseAttendance($class_id, $course_id, $current_school_session_id);
            }
            $data = ['attendances' => $attendances];

            return view('attendances.view', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function showStudentAttendance($id)
    {
        $user = Auth::user();
        $student = \App\Models\User::find($id);

        if (!\App\Classes\AcademicGate::canViewAttendance($student)) {
            return redirect()->back()->with('error', 'Academic records are temporarily withheld due to outstanding balance.');
        }

        if ($user->hasRole('Student') && $user->id != $id) {
            return abort(403);
        }

        // Teacher Check: Ensure teacher is assigned to the student's class/section
        if ($user->hasRole('Teacher')) {
            $studentModel = \App\Models\User::find($id)->student; // Get Student info to find class/section
            // Note: User::find($id) returns the User model, ->student returns the student record with class_id/section_id

            if ($studentModel) {
                $isAssigned = AssignedTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $this->getSchoolCurrentSession())
                    ->where('class_id', $studentModel->class_id)
                    ->where(function ($q) use ($studentModel) {
                        $q->where('section_id', $studentModel->section_id)
                            ->orWhereNull('section_id'); // Class Teacher
                    })
                    ->exists();

                if (!$isAssigned) {
                    // Debug info for 403
                    $message = "Teacher ID: " . $user->id .
                        "\nTarget Student Class: " . $studentModel->class_id .
                        "\nTarget Student Section: " . $studentModel->section_id .
                        "\nAuthorized: No (Strict Scoping)";
                    session()->flash('debug_message', $message);
                    abort(403);
                }
            }
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $attendanceRepository = new AttendanceRepository();
        $attendances = $attendanceRepository->getStudentAttendance($current_school_session_id, $id);
        $student = $this->userRepository->findStudent($id);

        $data = [
            'attendances' => $attendances,
            'student' => $student,
        ];

        return view('attendances.attendance', $data);
    }
}
