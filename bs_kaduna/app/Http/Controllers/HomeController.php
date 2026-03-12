<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Repositories\NoticeRepository;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\PromotionRepository;
use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $userRepository;
    protected $promotionRepository;
    protected $noticeRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserInterface $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        PromotionRepository $promotionRepository,
        NoticeRepository $noticeRepository
    ) {
        // $this->middleware('auth');
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->promotionRepository = $promotionRepository;
        $this->noticeRepository = $noticeRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasRole('Accountant')) {
            return redirect()->route('accounting.dashboard');
        }

        if (Auth::user()->hasRole('Student')) {
            return redirect()->route('student.dashboard');
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $classCount = $this->schoolClassRepository->getAllBySession($current_school_session_id)->count();

        $studentCount = $this->userRepository->getAllStudentsBySessionCount($current_school_session_id);

        $maleStudentsBySession = $this->promotionRepository->getMaleStudentsBySessionCount($current_school_session_id);

        $teacherCount = $this->userRepository->getAllTeachers()->count();

        $notices = $this->noticeRepository->getAll($current_school_session_id);

        $user = Auth::user();
        $absentStaff = [];
        $absentStudents = [];
        $isSchoolDay = Carbon::now()->isWeekday();

        $teacherAssignedClassesCount = 0;
        $teacherAssignedStudentsCount = 0;
        $teacherTodayAttendanceDone = false;

        if ($user->hasRole('Teacher')) {
            // Count classes where teacher is assigned (Section Teacher or Course Teacher)
            $teacherAssignedClassesCount = AssignedTeacher::where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->distinct('class_id')
                ->count('class_id');

            // Get total unique students in those classes/sections
            $assignedSections = AssignedTeacher::where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->pluck('section_id')
                ->unique();

            $teacherAssignedStudentsCount = \App\Models\Promotion::where('session_id', $current_school_session_id)
                ->whereIn('section_id', $assignedSections)
                ->distinct('student_id')
                ->count('student_id');

            // Quick check if attendance is done today for their assigned sections
            if ($isSchoolDay) {
                $attendanceTakenCount = Attendance::whereIn('section_id', $assignedSections)
                    ->whereDate('created_at', Carbon::today())
                    ->distinct('section_id')
                    ->count('section_id');

                if ($assignedSections->count() > 0 && $attendanceTakenCount >= $assignedSections->count()) {
                    $teacherTodayAttendanceDone = true;
                }
            }
        }

        if ($isSchoolDay && $user->hasRole('Admin')) {
            // Staff Absence: All staff who haven't checked in today
            $today = Carbon::today();
            $staffIdsWithAttendance = \App\Models\StaffAttendance::where('date', $today->toDateString())
                ->pluck('user_id')
                ->toArray();

            $absentStaff = \App\Models\User::whereIn('role', ['staff', 'librarian'])
                ->whereNotIn('id', $staffIdsWithAttendance)
                ->get();

            // Student Absence: Students marked 'Absent' in the attendances table for today
            $absentStudents = Attendance::with('student', 'schoolClass')
                ->whereDate('created_at', $today)
                ->where('status', 'Absent')
                ->get();
        }

        $outstandingBalance = 0;
        if ($user->hasRole('Student')) {
            $outstandingBalance = $user->getTotalOutstandingBalance();
        }

        $data = [
            'classCount' => $classCount,
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'notices' => $notices,
            'maleStudentsBySession' => $maleStudentsBySession,
            'absentStaff' => $absentStaff,
            'absentStudents' => $absentStudents,
            'isSchoolDay' => $isSchoolDay,
            'teacherAssignedClassesCount' => $teacherAssignedClassesCount,
            'teacherAssignedStudentsCount' => $teacherAssignedStudentsCount,
            'teacherTodayAttendanceDone' => $teacherTodayAttendanceDone,
            'outstandingBalance' => $outstandingBalance
        ];

        return view('home', $data);
    }
}
