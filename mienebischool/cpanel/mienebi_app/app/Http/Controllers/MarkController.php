<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\SectionInterface;
use App\Repositories\ExamRepository;
use App\Repositories\MarkRepository;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\GradeRuleRepository;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\AcademicSettingInterface;
use App\Repositories\GradingSystemRepository;
use App\Models\SchoolClass;
use App\Models\AssignedTeacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ExamRule;

class MarkController extends Controller
{
    use SchoolSession;

    protected $academicSettingRepository;
    protected $userRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $courseRepository;
    protected $semesterRepository;
    protected $schoolSessionRepository;

    public function __construct(
        AcademicSettingInterface $academicSettingRepository,
        UserInterface $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        CourseInterface $courseRepository,
        SemesterInterface $semesterRepository
    ) {
        $this->academicSettingRepository = $academicSettingRepository;
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->courseRepository = $courseRepository;
        $this->semesterRepository = $semesterRepository;
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

        return SchoolClass::whereRaw('1 = 0');
    }

    public function index(Request $request)
    {
        if (!Auth::user()->can('manage marks') && !Auth::user()->can('view marks')) {
            abort(403);
        }

        $class_id = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);
        $course_id = $request->query('course_id', 0);
        $semester_id = $request->query('semester_id', 0);

        $current_school_session_id = $this->getSchoolCurrentSession();

        // Security Check
        if (Auth::user()->hasRole('Teacher') && $class_id > 0) {
            $isAllowed = $this->getAccessibleClasses()->where('id', $class_id)->exists();
            if (!$isAllowed)
                abort(403, 'Unauthorized access to this class.');
        }

        $semesters = $this->semesterRepository->getAll($current_school_session_id);
        $school_classes = $this->getAccessibleClasses()->get();

        $markRepository = new MarkRepository();
        $marks = $markRepository->getAllFinalMarks($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);

        if (!$marks) {
            $marks = [];
        }

        $gradingSystemRules = [];
        if (count($marks) > 0) {
            $gradingSystemRepository = new GradingSystemRepository();
            $gradingSystem = $gradingSystemRepository->getGradingSystem($current_school_session_id, $semester_id, $class_id);
            if ($gradingSystem) {
                $gradeRulesRepository = new GradeRuleRepository();
                $gradingSystemRules = $gradeRulesRepository->getAll($current_school_session_id, $gradingSystem->id);

                foreach ($marks as $mark_key => $mark) {
                    foreach ($gradingSystemRules as $key => $gradingSystemRule) {
                        if ($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                            $marks[$mark_key]['point'] = $gradingSystemRule->point;
                            $marks[$mark_key]['grade'] = $gradingSystemRule->grade;
                        }
                    }
                }
            }
        }

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'semesters' => $semesters,
            'classes' => $school_classes,
            'marks' => $marks,
            'grading_system_rules' => $gradingSystemRules,
            'class_id' => $class_id,
            'section_id' => $section_id,
            'course_id' => $course_id,
            'semester_id' => $semester_id
        ];

        return view('marks.results', $data);
    }

    public function create(Request $request)
    {
        if (!Auth::user()->can('manage marks')) {
            abort(403);
        }

        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');
        $semester_id = $request->query('semester_id', 0);
        $current_school_session_id = $this->getSchoolCurrentSession();

        // Strict Ownership Check
        if (Auth::user()->hasRole('Teacher')) {
            $exists = AssignedTeacher::where('teacher_id', Auth::id())
                ->where('class_id', $class_id)
                ->where('session_id', $current_school_session_id)
                ->exists();

            if (!$exists)
                abort(403, 'You are not assigned to this class.');

            if ($course_id) {
                $courseExists = AssignedTeacher::where('teacher_id', Auth::id())
                    ->where('class_id', $class_id)
                    ->where('course_id', $course_id)
                    ->where('session_id', $current_school_session_id)
                    ->exists();
                if (!$courseExists)
                    abort(403, 'You are not assigned to this course.');
            }
        }

        try {
            $academic_setting = $this->academicSettingRepository->getAcademicSetting();
            $examRepository = new ExamRepository();
            $examRepository->ensureExamsExistForClass($current_school_session_id, $semester_id, $class_id);
            $exams = $examRepository->getAll($current_school_session_id, $semester_id, $class_id);

            $markRepository = new MarkRepository();
            $studentsWithMarks = $markRepository->getAll($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);
            $studentsWithMarks = $studentsWithMarks->groupBy('student_id');

            $sectionStudents = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);

            $final_marks_submitted = false;
            $final_marks_submit_count = $markRepository->getFinalMarksCount($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);

            if ($final_marks_submit_count > 0) {
                $final_marks_submitted = true;
            }

            $data = [
                'academic_setting' => $academic_setting,
                'exams' => $exams,
                'students_with_marks' => $studentsWithMarks,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'course_id' => $course_id,
                'semester_id' => $semester_id,
                'final_marks_submitted' => $final_marks_submitted,
                'sectionStudents' => $sectionStudents,
                'current_school_session_id' => $current_school_session_id,
            ];

            return view('marks.create', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('manage marks')) {
            abort(403);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        // Validation with Scope
        $request->validate([
            'class_id' => [
                'required',
                function ($attribute, $value, $fail) use ($current_school_session_id) {
                    if (Auth::user()->hasRole('Admin'))
                        return;
                    $exists = AssignedTeacher::where('teacher_id', Auth::id())
                        ->where('class_id', $value)
                        ->where('session_id', $current_school_session_id)
                        ->exists();
                    if (!$exists)
                        $fail('Unauthorized class.');
                }
            ],
            'course_id' => [
                'required',
                function ($attribute, $value, $fail) use ($request, $current_school_session_id) {
                    if (Auth::user()->hasRole('Admin'))
                        return;
                    $exists = AssignedTeacher::where('teacher_id', Auth::id())
                        ->where('course_id', $value)
                        ->where('class_id', $request->class_id)
                        ->where('session_id', $current_school_session_id)
                        ->exists();
                    if (!$exists)
                        $fail('Unauthorized course.');
                }
            ]
        ]);

        // Check if marks are already finalized for this batch
        $isFinalized = \App\Models\FinalMark::where('session_id', $current_school_session_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($isFinalized) {
            return back()->withError('Marks have been finalized and locked. You cannot edit them anymore.');
        }

        $rows = [];
        $errors = [];
        if ($request->student_mark) {
            // 1. Get all relevant Exam IDs from the request
            $examIds = [];
            foreach ($request->student_mark as $stm) {
                foreach (array_keys($stm) as $examId) {
                    $examIds[] = $examId;
                }
            }
            $examIds = array_unique($examIds);

            // 2. Fetch ExamRules for these exams
            $examRules = ExamRule::whereIn('exam_id', $examIds)->get()->keyBy('exam_id');

            foreach ($request->student_mark as $id => $stm) {
                foreach ($stm as $exam => $breakdown) {
                    $row = [];
                    $row['class_id'] = $request->class_id;
                    $row['student_id'] = $id;

                    $cleanBreakdown = array_map('floatval', (array) $breakdown); // Use floatval for decimals

                    // --- Validation Start ---
                    if (isset($examRules[$exam])) {
                        $rule = $examRules[$exam];
                        $definedBreakdown = $rule->marks_breakdown; // Array of [name, weight]

                        if ($definedBreakdown) {
                            foreach ($definedBreakdown as $component) {
                                $slug = Str::slug($component['name'], '_');
                                $weight = $component['weight'];

                                // Check if this component is in the submitted breakdown
                                if (isset($cleanBreakdown[$slug])) {
                                    if ($cleanBreakdown[$slug] > $weight) {
                                        $student = $this->userRepository->findStudent($id); // Helper to get student name
                                        $studentName = $student ? $student->first_name . ' ' . $student->last_name : 'Student #' . $id;
                                        $errors[] = "Mark for {$component['name']} for {$studentName} ({$cleanBreakdown[$slug]}) exceeds maximum weight of {$weight}.";
                                    }
                                }
                            }
                        }
                    }
                    // --- Validation End ---

                    $total = array_sum($cleanBreakdown);
                    $row['marks'] = $total;
                    $row['breakdown_marks'] = $cleanBreakdown;

                    $row['exam_mark'] = $cleanBreakdown['final_exam'] ?? 0;
                    $row['ca1_mark'] = $cleanBreakdown['ca_1'] ?? 0;
                    $row['ca2_mark'] = $cleanBreakdown['ca_2'] ?? 0;

                    $row['section_id'] = $request->section_id;
                    $row['course_id'] = $request->course_id;
                    $row['session_id'] = $current_school_session_id;
                    $row['exam_id'] = $exam;

                    $rows[] = $row;
                }
            }
        }

        if (count($errors) > 0) {
            return back()->withError(implode('<br>', $errors))->withInput();
        }

        try {
            $markRepository = new MarkRepository();
            $markRepository->create($rows);
            return back()->with('status', 'Saving marks was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function showFinalMark(Request $request)
    {
        if (!Auth::user()->can('manage marks'))
            abort(403);

        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');
        $semester_id = $request->query('semester_id', 0);
        $current_school_session_id = $this->getSchoolCurrentSession();

        // Scope Check
        if (Auth::user()->hasRole('Teacher')) {
            $exists = AssignedTeacher::where('teacher_id', Auth::id())
                ->where('class_id', $class_id)
                ->where('session_id', $current_school_session_id)
                ->exists();
            if (!$exists)
                abort(403, 'Unauthorized.');
        }

        $markRepository = new MarkRepository();
        $studentsWithMarks = $markRepository->getAll($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);
        $studentsWithMarks = $studentsWithMarks->groupBy('student_id');

        $data = [
            'students_with_marks' => $studentsWithMarks,
            'class_id' => $class_id,
            'class_name' => $request->query('class_name'),
            'section_id' => $section_id,
            'section_name' => $request->query('section_name'),
            'course_id' => $course_id,
            'course_name' => $request->query('course_name'),
            'semester_id' => $semester_id,
            'current_school_session_id' => $current_school_session_id,
        ];

        return view('marks.submit-final-marks', $data);
    }

    public function storeFinalMark(Request $request)
    {
        if (!Auth::user()->can('manage marks'))
            abort(403);

        $current_school_session_id = $this->getSchoolCurrentSession();

        $request->validate([
            'class_id' => [
                'required',
                function ($attribute, $value, $fail) use ($current_school_session_id) {
                    if (Auth::user()->hasRole('Admin'))
                        return;
                    $exists = AssignedTeacher::where('teacher_id', Auth::id())
                        ->where('class_id', $value)
                        ->where('session_id', $current_school_session_id)
                        ->exists();
                    if (!$exists)
                        $fail('Unauthorized class.');
                }
            ]
        ]);

        $rows = [];
        if ($request->calculated_mark) {
            foreach ($request->calculated_mark as $id => $cmark) {
                $row = [];
                $row['class_id'] = $request->class_id;
                $row['student_id'] = $id;
                $row['calculated_marks'] = $cmark;
                $row['final_marks'] = $request->final_mark[$id];
                $row['note'] = $request->note[$id];
                $row['section_id'] = $request->section_id;
                $row['course_id'] = $request->course_id;
                $row['session_id'] = $current_school_session_id;
                $row['semester_id'] = $request->semester_id;

                $rows[] = $row;
            }
        }
        try {
            $markRepository = new MarkRepository();
            $markRepository->storeFinalMarks($rows);
            return back()->with('status', 'Submitting final marks was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function showCourseMark(Request $request)
    {
        $session_id = $request->query('session_id');
        $student_id = $request->query('student_id');

        if (Auth::user()->hasRole('Student')) {
            if (Auth::id() != $student_id)
                abort(403);

            if (Auth::user()->getTotalOutstandingBalance() > 0) {
                return view('marks.restricted', ['balance' => Auth::user()->getTotalOutstandingBalance()]);
            }
        }

        $semester_id = $request->query('semester_id');
        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');
        $course_name = $request->query('course_name');

        $markRepository = new MarkRepository();
        $marks = $markRepository->getAllByStudentId($session_id, $semester_id, $class_id, $section_id, $course_id, $student_id);
        $finalMarks = $markRepository->getAllFinalMarksByStudentId($session_id, $student_id, $semester_id, $class_id, $section_id, $course_id);

        if (!$finalMarks)
            abort(404);

        $gradingSystemRepository = new GradingSystemRepository();
        $gradingSystem = $gradingSystemRepository->getGradingSystem($session_id, $semester_id, $class_id);

        if (!$gradingSystem)
            abort(404);

        $gradeRulesRepository = new GradeRuleRepository();
        $gradingSystemRules = $gradeRulesRepository->getAll($session_id, $gradingSystem->id);

        if (!$gradingSystemRules)
            abort(404);

        foreach ($finalMarks as $mark_key => $mark) {
            foreach ($gradingSystemRules as $key => $gradingSystemRule) {
                if ($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                    $finalMarks[$mark_key]['point'] = $gradingSystemRule->point;
                    $finalMarks[$mark_key]['grade'] = $gradingSystemRule->grade;
                }
            }
        }

        $data = [
            'marks' => $marks,
            'final_marks' => $finalMarks,
            'course_name' => $course_name,
        ];

        return view('marks.student', $data);
    }
}
