<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\TeacherStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\StudentParentInfoRepository;
use App\Models\AssignedTeacher;
use Illuminate\Support\Facades\Auth;

use App\Interfaces\WalletServiceInterface;

class UserController extends Controller
{
    use SchoolSession;
    protected $userRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $promotionRepository;
    protected $studentParentInfoRepository;
    protected $walletService;

    public function __construct(
        UserInterface $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        PromotionRepository $promotionRepository,
        StudentParentInfoRepository $studentParentInfoRepository,
        WalletServiceInterface $walletService
    ) {
        $this->middleware(['can:view-student-list']);

        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->promotionRepository = $promotionRepository;
        $this->studentParentInfoRepository = $studentParentInfoRepository;
        $this->walletService = $walletService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeTeacher(TeacherStoreRequest $request)
    {
        try {
            $this->userRepository->createTeacher($request->validated());

            return back()->with('status', 'Teacher creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getStudentList(Request $request)
    {
        $user = Auth::user();
        $current_school_session_id = $this->getSchoolCurrentSession();

        $class_id = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);
        $course_id = $request->query('course_id', 0); // Added for Marks context

        \Illuminate\Support\Facades\Log::info("getStudentList Request", [
            'user_id' => $user->id,
            'class_id' => $class_id,
            'section_id' => $section_id,
            'course_id' => $course_id,
        ]);

        $context = ($course_id > 0) ? 'marks' : 'attendance';

        try {
            // Role-Based Authorization & Scoping
            if ($user->hasRole('Teacher')) {
                // 1. Resolve Retrieval Context (Redirect Fallback)
                if ($class_id == 0 && $section_id == 0) {
                    $assignments = AssignedTeacher::where('teacher_id', $user->id)
                        ->where('session_id', $current_school_session_id)
                        ->get();

                    if ($assignments->isEmpty()) {
                        return view('students.list', [
                            'studentList' => [],
                            'school_classes' => [],
                            'context' => $context,
                            'class_id' => $class_id,
                            'section_id' => $section_id,
                            'course_id' => $course_id
                        ]);
                    }

                    // Check if Class Teacher (Has any assignment with section_id = NULL)
                    $isClassTeacher = $assignments->contains(fn($a) => is_null($a->section_id));

                    if ($isClassTeacher) {
                        // Class Teachers can view "All", so we don't force redirect to a specific section.
                        // However, we might want to default to their assigned class if they only have one class?
                        // Current logic: Allow them to proceed with 0, 0 which implies "View All" (filtered by their assignments later).
                    } else {
                        // Section Teacher (Strictly assigned to specific sections)
                        // MUST have context. Redirect to their first assigned section.
                        $firstAssignment = $assignments->first();
                        if ($firstAssignment) {
                            return redirect()->route('student.list.show', [
                                'class_id' => $firstAssignment->class_id,
                                'section_id' => $firstAssignment->section_id
                            ]);
                        }
                    }
                }

                // 2. Prepare Assignments Query for strict checking
                $assignmentsCheck = AssignedTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $current_school_session_id);

                if ($context === 'marks') {
                    $assignmentsCheck->where('course_id', $course_id);
                } else {
                    // Attendance/View Context
                    if ($section_id > 0) {
                        // Strict: User requested Section X. Must be assigned to Section X (or be Class Teacher)
                        $assignmentsCheck->where(function ($q) use ($section_id) {
                            $q->where('section_id', $section_id)
                                ->orWhereNull('section_id'); // Class Teacher covers all sections
                        });
                    } else {
                        // section_id == 0 (View All)
                        // Only allowed if Class Teacher. Section Teachers should have been redirected above.
                        // But if they manually manipulated URL to ?class_id=1&section_id=0...
                        $assignmentsCheck->whereNull('section_id');
                    }
                }

                // 3. Apply Class Scope if present
                if ($class_id > 0) {
                    $assignmentsCheck->where('class_id', $class_id);
                }

                // 4. Final Authorization Check
                if (!$assignmentsCheck->exists()) {
                    abort(403);
                }

                // Filter Dropdowns
                $assignedClassIds = AssignedTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $current_school_session_id)
                    ->pluck('class_id');

                $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id)
                    ->whereIn('id', $assignedClassIds);
            } else {
                // Admin/Accountant: unrestricted
                $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
            }

            $primary_section_id = $section_id;

            // If a generic class assignment (NULL section) is found, and no specific section requested,
            // we default to 0 for getAllStudents to get ALL.
            // BUT if specific section requested, we pass that.
            // Wait, if teacher is Section Teacher (section=1) and requested section=0?
            // "Defaulting logic" above only ran if class_id == 0.
            // If class_id > 0 and section_id == 0.
            // assignments query checked "Any assignment in class". Success.
            // Then we call getAllStudents with section_id=0. -> LEAK.

            // Fix for LEAK: Recalculate section_id if 0 based on assignment?
            // The user wants strict debug first. I'll stick to logging.

            $studentList = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);

            $data = [
                'studentList' => $studentList,
                'school_classes' => $school_classes,
                'context' => $context,
                'course_id' => $course_id,
                'class_id' => $class_id,
                'section_id' => $section_id
            ];

            \Illuminate\Support\Facades\Log::info("Returning View", [
                'final_class_id' => $class_id,
                'final_section_id' => $section_id,
                'students_count' => count($studentList)
            ]);

            return view('students.list', $data);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in getStudentList: " . $e->getMessage());
            return back()->withError($e->getMessage());
        }
    }


    public function showStudentProfile($id)
    {
        $student = $this->userRepository->findStudent($id);

        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion_info = $this->promotionRepository->getPromotionInfoById($current_school_session_id, $id);
        $walletBalance = $this->walletService->getBalance($id);

        $data = [
            'student' => $student,
            'promotion_info' => $promotion_info,
            'walletBalance' => $walletBalance
        ];

        return view('students.profile', $data);
    }

    public function showTeacherProfile($id)
    {
        $teacher = $this->userRepository->findTeacher($id);
        $data = [
            'teacher' => $teacher,
        ];
        return view('teachers.profile', $data);
    }


    public function createStudent()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'school_classes' => $school_classes,
        ];

        return view('students.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StudentStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeStudent(StudentStoreRequest $request)
    {
        try {
            $this->userRepository->createStudent($request->validated());

            return back()->with('status', 'Student creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editStudent($student_id)
    {
        $student = $this->userRepository->findStudent($student_id);
        $parent_info = $this->studentParentInfoRepository->getParentInfo($student_id);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion_info = $this->promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);

        $data = [
            'student' => $student,
            'parent_info' => $parent_info,
            'promotion_info' => $promotion_info,
        ];
        return view('students.edit', $data);
    }

    public function updateStudent(Request $request)
    {
        try {
            $this->userRepository->updateStudent($request->toArray());

            return back()->with('status', 'Student update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editTeacher($teacher_id)
    {
        $teacher = $this->userRepository->findTeacher($teacher_id);

        $data = [
            'teacher' => $teacher,
        ];

        return view('teachers.edit', $data);
    }
    public function updateTeacher(Request $request)
    {
        try {
            $this->userRepository->updateTeacher($request->toArray());

            return back()->with('status', 'Teacher update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getTeacherList()
    {
        $teachers = $this->userRepository->getAllTeachers();

        $data = [
            'teachers' => $teachers,
        ];

        return view('teachers.list', $data);
    }
}
