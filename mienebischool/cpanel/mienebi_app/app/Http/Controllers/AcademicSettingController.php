<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\AcademicSettingInterface;
use App\Http\Requests\AttendanceTypeUpdateRequest;

class AcademicSettingController extends Controller
{
    use SchoolSession;
    protected $academicSettingRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $userRepository;
    protected $courseRepository;
    protected $semesterRepository;

    public function __construct(
        AcademicSettingInterface $academicSettingRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        UserInterface $userRepository,
        CourseInterface $courseRepository,
        SemesterInterface $semesterRepository
    ) {
        $this->middleware(['can:view academic settings']);

        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
        $this->semesterRepository = $semesterRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $latest_school_session = $this->schoolSessionRepository->getLatestSession();

        $academic_setting = $this->academicSettingRepository->getAcademicSetting();

        $school_sessions = $this->schoolSessionRepository->getAll();

        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $school_sections = $this->schoolSectionRepository->getAllBySession($current_school_session_id);

        $teachers = $this->userRepository->getAllTeachers();

        $courses = $this->courseRepository->getAll($current_school_session_id);

        $semesters = $this->semesterRepository->getAll($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'latest_school_session_id' => $latest_school_session->id,
            'academic_setting' => $academic_setting,
            'school_sessions' => $school_sessions,
            'school_classes' => $school_classes,
            'school_sections' => $school_sections,
            'teachers' => $teachers,
            'courses' => $courses,
            'semesters' => $semesters,
        ];

        return view('academics.settings', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  AttendanceTypeUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAttendanceType(AttendanceTypeUpdateRequest $request)
    {
        try {
            $this->academicSettingRepository->updateAttendanceType($request->validated());

            return back()->with('status', 'Attendance type update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function updateFinalMarksSubmissionStatus(Request $request)
    {
        try {
            $this->academicSettingRepository->updateFinalMarksSubmissionStatus($request);

            return back()->with('status', 'Final marks submission status update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function updateDefaultWeights(Request $request)
    {
        $request->validate([
            'names' => 'required|array',
            'weights' => 'required|array',
            'names.*' => 'required|string',
            'weights.*' => 'required|integer|min:0|max:100',
        ]);

        $names = $request->names;
        $weights = $request->weights;

        if (count($names) !== count($weights)) {
            return back()->withError('Invalid data submitted.');
        }

        if (array_sum($weights) != 100) {
            return back()->withError('The sum of weights must be exactly 100%.');
        }

        $breakdown = [];
        foreach ($names as $index => $name) {
            $breakdown[] = [
                'name' => $name,
                'weight' => (int) $weights[$index]
            ];
        }

        try {
            $this->academicSettingRepository->updateDefaultWeights(['marks_breakdown' => $breakdown]);

            return back()->with('status', 'Default weights update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function updateFinancialWithholding(Request $request)
    {
        try {
            $this->academicSettingRepository->updateFinancialWithholding($request);

            return back()->with('status', 'Financial withholding status update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function updateFinalGrades(Request $request)
    {
        try {
            $sessionId = $this->getSchoolCurrentSession();
            $finalGradeClassIds = $request->input('final_grade_classes', []);
            $this->schoolClassRepository->updateFinalGradeDesignations($finalGradeClassIds, $sessionId);

            return back()->with('status', 'Graduation-eligible classes updated successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
