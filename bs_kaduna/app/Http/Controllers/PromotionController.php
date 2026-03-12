<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\SchoolSessionInterface;

class PromotionController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $userRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $promotionRepository;
    protected $promotionService;

    /**
     * Create a new Controller instance
     */
    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        UserInterface $userRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        PromotionRepository $promotionRepository,
        \App\Services\PromotionService $promotionService
    ) {
        $this->middleware(['auth', 'role:Admin|Teacher'])->except(['studentProjection']);

        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->userRepository = $userRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->promotionRepository = $promotionRepository;
        $this->promotionService = $promotionService;
    }

    /**
     * Promotion Settings (Admin)
     */
    public function policySettings(Request $request)
    {
        if (!Auth::user()->hasRole('Admin'))
            abort(403);

        $session_id = $this->getSchoolCurrentSession();
        $classes = $this->schoolClassRepository->getAllBySession($session_id);

        $policies = \App\Models\PromotionPolicy::where('session_id', $session_id)->get()->keyBy('class_id');

        return view('promotions.policy', compact('classes', 'policies', 'session_id'));
    }

    public function storePolicy(Request $request)
    {
        if (!Auth::user()->hasRole('Admin'))
            abort(403);

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'calculation_method' => 'required|in:cumulative,weighted_term_3',
            'passing_threshold' => 'required|numeric|min:0|max:100',
            'mandatory_course_ids' => 'nullable|array',
            'probation_logic' => 'required|in:promote_with_tag,retain'
        ]);

        \App\Models\PromotionPolicy::updateOrCreate(
            [
                'class_id' => $request->class_id,
                'session_id' => $this->getSchoolCurrentSession()
            ],
            $request->only(['calculation_method', 'passing_threshold', 'mandatory_course_ids', 'probation_logic'])
        );

        return back()->with('status', 'Promotion policy updated successfully.');
    }

    /**
     * Teacher Review Board
     */
    public function reviewBoard(Request $request)
    {
        $user = Auth::user();
        if ($user->hasRole('Staff') || $user->role == 'staff') {
            abort(403, 'Staff members are not authorized to view the Promotion Review Board.');
        }

        $session_id = $this->getSchoolCurrentSession();
        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');

        $classes = $this->schoolClassRepository->getAllBySession($session_id);
        $sections = $class_id ? $this->schoolSectionRepository->getAllByClassId($class_id) : [];

        $reviews = [];
        if ($class_id && $section_id) {
            // Generate/Refresh results if not existing
            $this->promotionService->generateBatchResults($session_id, (int) $class_id, (int) $section_id);

            $reviews = \App\Models\PromotionReview::with('student')
                ->where('session_id', $session_id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->get();
        }

        return view('promotions.review-board', compact('classes', 'sections', 'reviews', 'class_id', 'section_id'));
    }

    /**
     * Update Manual Override
     */
    public function updateReview(Request $request)
    {
        $user = Auth::user();
        if ($user->hasRole('Staff') || $user->role == 'staff') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'review_id' => 'required|exists:promotion_reviews,id',
            'final_status' => 'required|in:promoted,retained,probation',
            'override_comment' => 'required|string|min:5'
        ]);

        $review = \App\Models\PromotionReview::findOrFail($request->review_id);

        $review->update([
            'final_status' => $request->final_status,
            'is_overridden' => $request->final_status !== $review->calculated_status,
            'override_comment' => $request->override_comment,
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Finalize Batch
     */
    public function finalizeBatch(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['Admin', 'Teacher', 'Super Admin']))
            abort(403, 'Unauthorized to finalize batches.');

        $request->validate([
            'class_id' => 'required|integer|exists:school_classes,id',
            'section_id' => 'required|integer|exists:sections,id',
        ]);

        $session_id = $this->getSchoolCurrentSession();
        $class_id = $request->class_id;
        $section_id = $request->section_id;

        $reviews = \App\Models\PromotionReview::where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('is_finalized', false)
            ->get();

        foreach ($reviews as $review) {
            $review->update([
                'is_finalized' => true,
                'reviewer_id' => $review->reviewer_id ?: Auth::id(),
                'reviewed_at' => $review->reviewed_at ?: now(),
            ]);
        }

        return back()->with('status', 'Promotion decisions finalized successfully. Use manual promotion to place students into the destination session.');
    }

    /**
     * Student Projection View
     */
    public function studentProjection(Request $request)
    {
        $user = Auth::user();
        $session_id = $this->getSchoolCurrentSession();

        $performance = $this->promotionService->calculateStudentPerformance($user->id, $session_id);

        $review = \App\Models\PromotionReview::where('student_id', $user->id)
            ->where('session_id', $session_id)
            ->first();

        return view('promotions.student-projection', compact('performance', 'review'));
    }
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $class_id = $request->query('class_id', 0);

        $previousSession = $this->schoolSessionRepository->getPreviousSession();

        if (count($previousSession) < 1) {
            return back()->withError('No previous session');
        }

        $previousSessionClasses = $this->promotionRepository->getClasses($previousSession['id']);

        $previousSessionSections = $this->promotionRepository->getSections($previousSession['id'], $class_id);

        $current_school_session_id = $this->getSchoolCurrentSession();
        $sectionPromotionStatus = [];
        foreach ($previousSessionSections as $previousSessionSection) {
            $sectionPromotionStatus[$previousSessionSection->section_id] = $this->promotionRepository->isSectionPromotedToSession(
                (int) $previousSession['id'],
                (int) $current_school_session_id,
                (int) $class_id,
                (int) $previousSessionSection->section_id
            );
        }

        $data = [
            'previousSessionClasses' => $previousSessionClasses,
            'class_id' => $class_id,
            'previousSessionSections' => $previousSessionSections,
            'sectionPromotionStatus' => $sectionPromotionStatus,
            'previousSessionId' => $previousSession['id'],
        ];

        return view('promotions.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $class_id = $request->query('previous_class_id');
        $section_id = $request->query('previous_section_id');
        $session_id = $request->query('previousSessionId');

        try {

            if ($class_id == null || $section_id == null || $session_id == null) {
                return abort(404);
            }

            $students = $this->userRepository->getAllStudents($session_id, $class_id, $section_id);

            $schoolClass = $this->schoolClassRepository->findById($class_id);
            $section = $this->schoolSectionRepository->findById($section_id);

            $latest_school_session = $this->schoolSessionRepository->getLatestSession();

            $school_classes = $this->schoolClassRepository->getAllBySession($latest_school_session->id);
            $reviews = \App\Models\PromotionReview::where('session_id', $session_id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->get()
                ->keyBy('student_id');

            $data = [
                'students' => $students,
                'schoolClass' => $schoolClass,
                'section' => $section,
                'school_classes' => $school_classes,
                'reviews' => $reviews,
            ];

            return view('promotions.promote', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $latest_school_session = $this->schoolSessionRepository->getLatestSession();
        $request->validate([
            'id_card_number' => 'required|array',
            'class_id' => 'required|array',
            'section_id' => 'required|array',
        ]);

        $id_card_numbers = $request->id_card_number;
        $classIds = array_values($request->class_id);
        $sectionIds = array_values($request->section_id);

        if (count($id_card_numbers) !== count($classIds) || count($id_card_numbers) !== count($sectionIds)) {
            return back()->withError('Promotion form data is inconsistent. Please reload and try again.');
        }

        $rows = [];
        $i = 0;
        foreach ($id_card_numbers as $student_id => $id_card_number) {
            $targetClassId = (int) ($classIds[$i] ?? 0);
            $targetSectionId = (int) ($sectionIds[$i] ?? 0);

            $targetClass = \App\Models\SchoolClass::where('id', $targetClassId)
                ->where('session_id', $latest_school_session->id)
                ->first();
            if (!$targetClass) {
                return back()->withError('One or more target classes do not belong to the destination session.');
            }

            $targetSection = \App\Models\Section::where('id', $targetSectionId)
                ->where('class_id', $targetClassId)
                ->where('session_id', $latest_school_session->id)
                ->first();
            if (!$targetSection) {
                return back()->withError('One or more target sections do not belong to the selected destination class.');
            }

            $row = [
                'student_id' => $student_id,
                'id_card_number' => $id_card_number,
                'class_id' => $targetClassId,
                'section_id' => $targetSectionId,
                'session_id' => $latest_school_session->id,
            ];
            array_push($rows, $row);
            $i++;
        }

        try {
            $this->promotionRepository->massPromotion($rows);

            return back()->with('status', 'Promoting students was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
