<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Routine;
use App\Models\Notice;
use App\Models\Promotion;
use App\Models\StudentFee;
use App\Models\StudentPayment;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;

class StudentPortalController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $walletService;

    public function __construct(SchoolSessionInterface $schoolSessionRepository, \App\Interfaces\WalletServiceInterface $walletService)
    {
        $this->middleware(['auth', 'role:Student']);
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->walletService = $walletService;
    }

    /**
     * Student Dashboard - Overview
     */
    public function dashboard()
    {
        $student = Auth::user();
        $current_session_id = $this->getSchoolCurrentSession();

        // Get student's current class/section
        $promotion = Promotion::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->with(['schoolClass', 'section'])
            ->first();

        // Recent attendance
        $recentAttendance = Attendance::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->latest()
            ->take(5)
            ->get();

        // Attendance summary
        $totalPresent = Attendance::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->where('status', 'Present')
            ->count();

        $totalAbsent = Attendance::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->where('status', 'Absent')
            ->count();

        // Recent notices
        $notices = Notice::where('session_id', $current_session_id)
            ->latest()
            ->take(3)
            ->get();

        // Corrected Source of Truth: Wallet Balance
        // If negative = Debt (Outstanding)
        // If positive = Credit
        $walletBalance = $this->walletService->getBalance($student->id);

        // Pass strictly the wallet balance. The View should handle the "Credit vs Debt" display logic.
        // We will pass 'walletBalance' instead of 'outstandingBalance' to be precise.

        return view('student.dashboard', compact(
            'student',
            'promotion',
            'recentAttendance',
            'totalPresent',
            'totalAbsent',
            'notices',
            'walletBalance'
        ));
    }

    /**
     * View full attendance history
     */
    public function attendance()
    {
        $student = Auth::user();
        $current_session_id = $this->getSchoolCurrentSession();

        // Apply Financial Withholding Gate
        // Apply Financial Withholding Gate
        if (!\App\Classes\AcademicGate::canViewResults($student)) {
            $withheld = true;
            // Pass empty paginator to prevent view crash
            $attendance = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        } else {
            $withheld = false;
            $attendance = Attendance::where('student_id', $student->id)
                ->where('session_id', $current_session_id)
                ->with(['schoolClass', 'section', 'course'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('student.attendance', compact('attendance', 'student', 'withheld'));
    }

    /**
     * View marks/grades
     */
    public function marks()
    {
        $student = Auth::user();
        $current_session_id = $this->getSchoolCurrentSession();

        $marks = Mark::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->with(['course', 'exam'])
            ->get();

        $semesters = \App\Models\Semester::where('session_id', $current_session_id)->get();

        $comments = \App\Models\StudentReportComment::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->get()
            ->keyBy('semester_id');

        return view('student.marks', compact('marks', 'student', 'comments', 'semesters'));
    }

    /**
     * View timetable/routine
     */
    public function timetable()
    {
        $student = Auth::user();
        $current_session_id = $this->getSchoolCurrentSession();

        // Get student's section
        $promotion = Promotion::where('student_id', $student->id)
            ->where('session_id', $current_session_id)
            ->first();

        $routines = [];
        if ($promotion) {
            $routines = Routine::where('section_id', $promotion->section_id)
                ->where('session_id', $current_session_id)
                ->with(['course'])
                ->orderBy('weekday')
                ->get();
        }

        return view('student.timetable', compact('routines', 'student'));
    }

    /**
     * View financial history (read-only)
     */
    public function fees()
    {
        $student = Auth::user();

        $fees = StudentFee::with(['feeHead', 'session', 'semester'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $payments = StudentPayment::with(['schoolClass', 'session', 'semester', 'receiver', 'studentFee.feeHead'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $walletBalance = $this->walletService->getBalance($student->id);

        return view('student.fees', compact('student', 'fees', 'payments', 'walletBalance'));
    }
}
