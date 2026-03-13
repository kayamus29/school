<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Course;
use App\Models\Semester;
use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\AttendanceSummaryOverride;
use App\Models\Promotion;
use App\Models\User;
use App\Models\SchoolSession as SchoolSessionModel;
use App\Models\StudentReportComment;
use App\Models\EndTermUpdate;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Models\StudentCourseExclusion;

class ResultsDashboardController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware(['auth']);
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Teacher / Class Teacher View
     */
    /**
     * Subject Teacher View
     */
    public function teacherView(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
            abort(403);
        }

        $session_id = $this->getSchoolCurrentSession();

        // Handle combined course_class parameter
        $course_class = $request->query('course_class');
        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');

        if ($course_class) {
            $parts = explode('|', $course_class);
            if (count($parts) === 3) {
                $course_id = $parts[0] !== '' ? $parts[0] : null;
                $class_id = $parts[1];
                $section_id = $parts[2];
            }
        }

        // Fetch subject assignments only (course_id NOT NULL)
        $assignments = AssignedTeacher::with(['schoolClass', 'section', 'course'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $session_id)
            ->whereNotNull('course_id')
            ->get();

        $semesters = Semester::where('session_id', $session_id)->orderBy('id')->get();

        $students = [];
        $results = [];

        if ($class_id && $course_id) {
            // Validate ownership
            if ($user->hasRole('Teacher')) {
                $isAssigned = $assignments->where('class_id', $class_id)->where('course_id', $course_id)->first();
                if (!$isAssigned && !$user->hasRole('Admin')) {
                    abort(403, 'Unauthorized access to this course/class.');
                }
            }

            // Get students in this section
            $students = Promotion::with('student')
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->get()
                ->pluck('student');

            $allowedStudentIds = StudentCourseExclusion::filterStudentIdsForCourse($students->pluck('id'), (int) $course_id, (int) $session_id);
            $students = $students->whereIn('id', $allowedStudentIds)->values();

            // Fetch all final marks for these students in this course across all semesters
            $results = FinalMark::where('course_id', $course_id)
                ->where('session_id', $session_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');
        }

        return view('results.teacher', compact('assignments', 'semesters', 'students', 'results', 'class_id', 'section_id', 'course_id'));
    }

    /**
     * Class/Section Teacher View
     */
    public function sectionView(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
            abort(403);
        }

        $session_id = $this->getSchoolCurrentSession();
        $section_id = $request->query('section_id');
        $student_id = $request->query('student_id');

        $sectionAssignments = AssignedTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $session_id)
            ->sectionLeadership()
            ->get();
        $sections = $this->expandManagedSections($sectionAssignments, $session_id);

        $students = [];
        $selectedStudent = null;
        $selectedPromotion = null;
        $results = [];
        $courses = [];
        $attendanceSummaries = collect();
        $canOverrideAttendanceSummary = false;
        $semesters = Semester::where('session_id', $session_id)->orderBy('id')->get();

        if ($section_id) {
            // Validate ownership
            if ($user->hasRole('Teacher')) {
                $isAssigned = $this->teacherCanManageSection($sectionAssignments, (int) $section_id);
                if (!$isAssigned && !$user->hasRole('Admin')) {
                    abort(403, 'Unauthorized access to this section.');
                }
            }

            // Fetch students in this section
            $students = Promotion::with('student')
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->get()
                ->pluck('student');

            if ($student_id) {
                $selectedStudent = $students->where('id', $student_id)->first();
                if ($selectedStudent) {
                    $promotion = Promotion::where('student_id', $student_id)
                        ->where('session_id', $session_id)
                        ->with(['schoolClass.courses', 'section', 'session'])
                        ->first();

                    if ($promotion) {
                        $selectedPromotion = $promotion;
                        $courses = StudentCourseExclusion::filterCoursesForStudent($promotion->schoolClass->courses, (int) $student_id, (int) $session_id);
                        $results = FinalMark::where('student_id', $student_id)
                            ->where('session_id', $session_id)
                            ->get()
                            ->groupBy('course_id');
                        $attendanceSummaries = $this->buildAttendanceSummaries($student_id, $session_id, $semesters, $promotion);
                        $canOverrideAttendanceSummary = $user->hasRole('Teacher')
                            && $this->teacherCanManageSection($sectionAssignments, (int) $promotion->section_id);

                        $comments = \App\Models\StudentReportComment::where('student_id', $student_id)
                            ->where('session_id', $session_id)
                            ->get()
                            ->keyBy('semester_id');
                    }
                }
            }
        }

        $comments = $comments ?? collect();
        $endTermUpdates = Schema::hasTable('end_term_updates')
            ? EndTermUpdate::where('session_id', $session_id)->get()->keyBy('semester_id')
            : collect();

        return view('results.section', compact('sections', 'students', 'selectedStudent', 'selectedPromotion', 'results', 'courses', 'semesters', 'section_id', 'student_id', 'comments', 'attendanceSummaries', 'canOverrideAttendanceSummary', 'endTermUpdates'));
    }

    private function expandManagedSections($assignments, int $sessionId)
    {
        return $assignments
            ->flatMap(function ($assignment) use ($sessionId) {
                if ($assignment->section_id) {
                    return collect([$assignment]);
                }

                return Section::with('schoolClass')
                    ->where('session_id', $sessionId)
                    ->where('class_id', $assignment->class_id)
                    ->get()
                    ->map(function ($section) use ($assignment) {
                        $expanded = clone $assignment;
                        $expanded->section_id = $section->id;
                        $expanded->setRelation('section', $section);
                        $expanded->setRelation('schoolClass', $section->schoolClass);

                        return $expanded;
                    });
            })
            ->unique(fn ($assignment) => $assignment->class_id . '-' . $assignment->section_id)
            ->values();
    }

    private function teacherCanManageSection($assignments, int $sectionId): bool
    {
        return $assignments->contains(function ($assignment) use ($sectionId) {
            return (int) $assignment->section_id === $sectionId || $assignment->section_id === null;
        });
    }

    /**
     * Student View
     */
    public function studentView(Request $request)
    {
        $student = Auth::user();
        if (!$student->hasRole('Student')) {
            abort(403);
        }

        $currentSessionId = $this->getSchoolCurrentSession();
        $availableSessions = $this->getAvailableResultSessionsForStudent($student, $currentSessionId);
        $session_id = (int) $request->query('session_id', $currentSessionId);

        if ($availableSessions->where('id', $session_id)->isEmpty()) {
            $session_id = (int) optional($availableSessions->first())->id ?: $currentSessionId;
        }

        $selectedSession = $availableSessions->firstWhere('id', $session_id)
            ?? SchoolSessionModel::find($session_id)
            ?? $availableSessions->first();
        $semesters = Semester::where('session_id', $session_id)->orderBy('id')->get();
        $selectedSemesterId = (int) $request->query('semester_id', 0);
        $selectedSemester = $semesters->firstWhere('id', $selectedSemesterId);
        $visibleSemesters = $selectedSemester ? collect([$selectedSemester]) : $semesters;
        $reportTermLabel = $selectedSemester
            ? $selectedSemester->semester_name
            : $this->resolveReportTermLabel($semesters, $session_id, $currentSessionId);

        $promotion = Promotion::where('student_id', $student->id)
            ->where('session_id', $session_id)
            ->with(['schoolClass.courses', 'section', 'session'])
            ->first();

        if (!\App\Classes\AcademicGate::canViewResults($student)) {
            $withheld = true;
            return view('results.student', compact(
                'student',
                'session_id',
                'semesters',
                'withheld',
                'promotion',
                'availableSessions',
                'selectedSession',
                'selectedSemesterId',
                'selectedSemester',
                'visibleSemesters',
                'reportTermLabel'
            ));
        }

        $finalResults = FinalMark::where('student_id', $student->id)
            ->where('session_id', $session_id)
            ->get()
            ->groupBy('course_id');

        $rawMarks = Mark::with('exam')
            ->where('student_id', $student->id)
            ->where('session_id', $session_id)
            ->get()
            ->groupBy('course_id');

        if ($promotion && $promotion->schoolClass) {
            $courses = StudentCourseExclusion::filterCoursesForStudent($promotion->schoolClass->courses, (int) $student->id, (int) $session_id);
        } else {
            $courseIds = $finalResults->keys()->merge($rawMarks->keys())->unique()->filter()->values();
            $courses = Course::whereIn('id', $courseIds)->orderBy('course_name')->get();
        }

        if (!$promotion && $courses->isEmpty()) {
            return view('results.student', compact(
                'student',
                'session_id',
                'semesters',
                'availableSessions',
                'selectedSession',
                'selectedSemesterId',
                'selectedSemester',
                'visibleSemesters',
                'reportTermLabel'
            ) + [
                'error' => $session_id === $currentSessionId
                    ? 'No active enrollment or result records found for the current session.'
                    : 'No result records found for the selected session.',
            ]);
        }

        $results = [];

        foreach ($courses as $course) {
            if (isset($finalResults[$course->id])) {
                $results[$course->id] = $finalResults[$course->id];
            } else {
                if (isset($rawMarks[$course->id])) {
                    $courseMarks = $rawMarks[$course->id];
                    $provisionalBySemester = $courseMarks->groupBy(function ($item) {
                        return $item->exam->semester_id;
                    });

                    $simulatedFinalMarks = collect();

                    foreach ($provisionalBySemester as $semesterId => $marks) {
                        $total = $marks->sum('marks');
                        $simulated = new FinalMark([
                            'student_id' => $student->id,
                            'course_id' => $course->id,
                            'semester_id' => $semesterId,
                            'session_id' => $session_id,
                            'final_marks' => $total,
                            'is_provisional' => true
                        ]);
                        $simulatedFinalMarks->push($simulated);
                    }

                    if ($simulatedFinalMarks->isNotEmpty()) {
                        $results[$course->id] = $simulatedFinalMarks;
                    }
                }
            }
        }

        $comments = StudentReportComment::where('student_id', $student->id)
            ->where('session_id', $session_id)
            ->get()
            ->keyBy('semester_id');
        $attendanceSummaries = $promotion
            ? $this->buildAttendanceSummaries($student->id, $session_id, $semesters, $promotion)
            : collect();
        $endTermUpdates = Schema::hasTable('end_term_updates')
            ? EndTermUpdate::where('session_id', $session_id)->get()->keyBy('semester_id')
            : collect();

        return view('results.student', compact(
            'student',
            'session_id',
            'semesters',
            'courses',
            'results',
            'promotion',
            'comments',
            'attendanceSummaries',
            'endTermUpdates',
            'availableSessions',
            'selectedSession',
            'selectedSemesterId',
            'selectedSemester',
            'visibleSemesters',
            'reportTermLabel'
        ));
    }

    /**
     * Admin View - Student Search
     */
    public function adminView(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403);
        }

        $session_id = $this->getSchoolCurrentSession();
        $student_id = $request->query('student_id');
        $student = null;
        $promotion = null;
        $results = [];
        $courses = [];
        $attendanceSummaries = collect();
        $semesters = Semester::where('session_id', $session_id)->orderBy('id')->get();

        if ($student_id) {
            $student = User::find($student_id);
            $promotion = Promotion::where('student_id', $student_id)
                ->where('session_id', $session_id)
                ->with(['schoolClass.courses', 'section', 'session'])
                ->first();

            if ($promotion) {
                $courses = StudentCourseExclusion::filterCoursesForStudent($promotion->schoolClass->courses, (int) $student_id, (int) $session_id);
                $results = FinalMark::where('student_id', $student_id)
                    ->where('session_id', $session_id)
                    ->get()
                    ->groupBy('course_id');
                $attendanceSummaries = $this->buildAttendanceSummaries($student_id, $session_id, $semesters, $promotion);

                $comments = \App\Models\StudentReportComment::where('student_id', $student_id)
                    ->where('session_id', $session_id)
                    ->get()
                    ->keyBy('semester_id');
            }
        }

        $allStudents = User::role('Student')->get();
        $comments = $comments ?? collect();
        $endTermUpdates = Schema::hasTable('end_term_updates')
            ? EndTermUpdate::where('session_id', $session_id)->get()->keyBy('semester_id')
            : collect();

        return view('results.admin', compact('student', 'promotion', 'results', 'courses', 'semesters', 'allStudents', 'comments', 'attendanceSummaries', 'endTermUpdates'));
    }

    /**
     * AJAX Breakdown for Modal
     */
    public function getBreakdownAjax(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'session_id' => 'nullable|exists:school_sessions,id',
        ]);

        $session_id = (int) $request->query('session_id', $this->getSchoolCurrentSession());

        if (Auth::user()->hasRole('Student')) {
            $allowedSessionIds = $this->getAvailableResultSessionsForStudent(Auth::user(), $this->getSchoolCurrentSession())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (!in_array($session_id, $allowedSessionIds, true)) {
                abort(403, 'Unauthorized result session access.');
            }
        }

        if (in_array((int) $request->course_id, StudentCourseExclusion::excludedCourseIdsForStudent((int) $request->student_id, (int) $session_id), true)) {
            return response()->json([
                'success' => true,
                'assessments' => [],
                'summary' => null,
            ]);
        }

        $marks = Mark::with('exam.examRule')
            ->where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->where('session_id', $session_id)
            ->whereHas('exam', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            })
            ->get();

        $finalMark = FinalMark::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->where('semester_id', $request->semester_id)
            ->where('session_id', $session_id)
            ->first();

        return response()->json([
            'success' => true,
            'assessments' => $marks,
            'summary' => $finalMark
        ]);
    }

    /**
     * Student View (React/Inertia)
     */
    public function studentViewReact()
    {
        $student = Auth::user();
        if (!$student->hasRole('Student')) {
            abort(403);
        }

        $session_id = $this->getSchoolCurrentSession();
        $semesters = Semester::where('session_id', $session_id)->orderBy('id')->get();

        // Apply Financial Withholding Gate
        if (!\App\Classes\AcademicGate::canViewResults($student)) {
            return Inertia::render('Results/StudentDashboard', [
                'student' => $student,
                'semesters' => $semesters,
                'courses' => [],
                'results' => [],
                'promotion' => null,
                'withheld' => true,
            ]);
        }

        // Get all courses student is registered in (via Promotion -> Class -> Courses)
        $promotion = Promotion::where('student_id', $student->id)
            ->where('session_id', $session_id)
            ->with(['schoolClass.courses', 'schoolClass', 'session'])
            ->first();

        if (!$promotion) {
            return Inertia::render('Results/StudentDashboard', [
                'student' => $student,
                'semesters' => $semesters,
                'courses' => [],
                'results' => [],
                'promotion' => null,
                'withheld' => false,
                'error' => 'No active enrollment found for current session.',
            ]);
        }

        $courses = StudentCourseExclusion::filterCoursesForStudent($promotion->schoolClass->courses, (int) $student->id, (int) $session_id);

        // Fetch all final marks for this student
        $results = FinalMark::where('student_id', $student->id)
            ->where('session_id', $session_id)
            ->get()
            ->groupBy('course_id')
            ->toArray();

        return Inertia::render('Results/StudentDashboard', [
            'student' => $student,
            'semesters' => $semesters,
            'courses' => $courses,
            'results' => $results,
            'promotion' => $promotion,
            'withheld' => false,
        ]);
    }

    private function buildAttendanceSummaries(int $studentId, int $sessionId, $semesters, Promotion $promotion)
    {
        $overrides = AttendanceSummaryOverride::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->get()
            ->keyBy('semester_id');

        return $semesters->mapWithKeys(function ($semester) use ($studentId, $sessionId, $promotion, $overrides) {
            $daysPresent = Attendance::query()
                ->where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->whereIn('status', ['on', 'present', 'Present'])
                ->whereBetween('created_at', [$semester->start_date, $semester->end_date])
                ->get()
                ->groupBy(fn ($attendance) => optional($attendance->created_at)->format('Y-m-d'))
                ->count();

            $override = $overrides->get($semester->id);

            return [
                $semester->id => [
                    'total_school_days' => (int) ($semester->total_school_days ?? 0),
                    'calculated_days_present' => $daysPresent,
                    'days_present' => $override ? (int) $override->days_present : $daysPresent,
                    'is_overridden' => (bool) $override,
                    'override_note' => $override->note ?? null,
                    'override_updated_by' => $override->updated_by ?? null,
                    'attendance_percentage' => ((int) ($semester->total_school_days ?? 0) > 0)
                        ? round((($override ? (int) $override->days_present : $daysPresent) / (int) $semester->total_school_days) * 100, 1)
                        : null,
                ],
            ];
        });
    }

    private function getAvailableResultSessionsForStudent(User $student, int $currentSessionId)
    {
        $sessionIds = Promotion::where('student_id', $student->id)->pluck('session_id')
            ->merge(FinalMark::where('student_id', $student->id)->pluck('session_id'))
            ->merge(Mark::where('student_id', $student->id)->pluck('session_id'))
            ->merge(StudentReportComment::where('student_id', $student->id)->pluck('session_id'))
            ->unique()
            ->filter()
            ->values();

        if ($sessionIds->isEmpty()) {
            $sessionIds = collect([$currentSessionId]);
        } elseif (!$sessionIds->contains($currentSessionId)) {
            $sessionIds->prepend($currentSessionId);
        }

        return SchoolSessionModel::whereIn('id', $sessionIds)->orderByDesc('id')->get();
    }

    private function resolveReportTermLabel($semesters, int $sessionId, int $currentSessionId): string
    {
        if ($sessionId !== $currentSessionId) {
            return 'Session Overview';
        }

        $today = now()->toDateString();
        $activeSemester = $semesters->first(function ($semester) use ($today) {
            return !empty($semester->start_date)
                && !empty($semester->end_date)
                && $today >= $semester->start_date
                && $today <= $semester->end_date;
        });

        return $activeSemester
            ? 'Current Term: ' . $activeSemester->semester_name
            : 'Session Overview';
    }
}
