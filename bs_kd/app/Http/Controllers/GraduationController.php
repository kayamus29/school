<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolClass;
use App\Services\GraduationService;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GraduationController extends Controller
{
    use SchoolSession;

    protected $graduationService;
    protected $schoolSessionRepository;

    public function __construct(GraduationService $graduationService, SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware(['auth', 'role:Admin']);
        $this->graduationService = $graduationService;
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Display the graduation dashboard.
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();

        // Find all final grade classes for THIS session
        $finalClasses = SchoolClass::where('is_final_grade', true)->where('session_id', $sessionId)->get();

        // Fetch ALL classes for this session for the settings modal
        $allClasses = SchoolClass::where('session_id', $sessionId)->get();

        $classIds = $finalClasses->pluck('id')->toArray();

        // Get students in these classes for the current session
        // We join with promotions to find who belongs to these classes now
        $students = User::role('Student')
            ->where('status', 'active')
            ->whereHas('promotions', function ($query) use ($sessionId, $classIds) {
                $query->where('session_id', $sessionId)
                    ->whereIn('class_id', $classIds);
            })
            ->with([
                'promotions' => function ($query) use ($sessionId) {
                    $query->where('session_id', $sessionId)->with('schoolClass');
                }
            ])
            ->get();

        $gradData = [];
        foreach ($students as $student) {
            $eval = $this->graduationService->evaluate($student, $sessionId);
            $gradData[] = [
                'student' => $student,
                'evaluation' => $eval,
                'current_class' => $student->promotions->first()->schoolClass->class_name ?? 'N/A'
            ];
        }

        return view('academics.graduation', [
            'gradData' => $gradData,
            'finalClasses' => $finalClasses,
            'allClasses' => $allClasses
        ]);
    }

    /**
     * Finalize graduation for a student.
     */
    public function finalize(Request $request, $id)
    {
        $student = User::findOrFail($id);
        $sessionId = $this->getSchoolCurrentSession();

        $eval = $this->graduationService->evaluate($student, $sessionId);

        if ($eval['status'] !== 'eligible_for_graduation') {
            return back()->with('error', 'Student is not eligible: ' . $eval['reason']);
        }

        try {
            $this->graduationService->finalize($student, Auth::id());
            return back()->with('status', "{$student->first_name} has been graduated successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Graduation failed: ' . $e->getMessage());
        }
    }
}
