<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassFee;
use App\Models\SchoolClass;
use App\Models\FeeHead;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Traits\SchoolSession as SchoolSessionTrait;
use App\Interfaces\SchoolSessionInterface;
use App\Models\BillingBatch;
use App\Services\BillingService;
use Exception;
use Illuminate\Support\Facades\Auth;

class ClassFeeController extends Controller
{
    use SchoolSessionTrait;

    protected $schoolSessionRepository;
    protected $billingService;

    public function __construct(SchoolSessionInterface $schoolSessionRepository, BillingService $billingService)
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->billingService = $billingService;
    }

    public function index()
    {
        // Get all classes
        $classes = SchoolClass::paginate(20);

        $allClasses = SchoolClass::all();
        $feeHeads = FeeHead::all();
        $sessions = SchoolSession::all();

        $current_session_id = $this->getSchoolCurrentSession();
        $currentSession = SchoolSession::find($current_session_id);

        // Get semesters for the current session, ordered exactly by date/id
        $semesters = Semester::where('session_id', $current_session_id)
            ->orderBy('id', 'asc') // Assuming 1st, 2nd, 3rd are created in order
            ->get();

        // Get recent billing history
        $billingHistory = BillingBatch::with(['session', 'semester', 'processor'])
            ->latest()
            ->take(20)
            ->get();

        return view('accounting.fees.class.index', compact(
            'classes',
            'allClasses',
            'feeHeads',
            'sessions',
            'semesters',
            'currentSession',
            'billingHistory'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'fee_head_id' => 'required|exists:fee_heads,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'session_id' => 'required|exists:school_sessions,id',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        try {
            // Check if this fee head is already assigned to this class in this session/semester
            $exists = ClassFee::where('class_id', $request->class_id)
                ->where('fee_head_id', $request->fee_head_id)
                ->where('session_id', $request->session_id)
                ->where('semester_id', $request->semester_id)
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'This Fee Head is already assigned to the selected Class for this Term.');
            }

            ClassFee::create($request->all());
            return redirect()->back()->with('success', 'Fee assigned to class successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error assigning fee: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $classFee = ClassFee::findOrFail($id);
            $classFee->delete();
            return redirect()->back()->with('success', 'Fee assignment removed successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error removing fee assignment: ' . $e->getMessage());
        }
    }

    public function generateBills(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:school_sessions,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        try {
            // Check if already finalized to prevent accidental overlap via UI
            // (Service layer also checks student-level idempotency)
            $existing = BillingBatch::where('session_id', $request->session_id)
                ->where('semester_id', $request->semester_id)
                ->where('status', 'finalized')
                ->exists();

            if ($existing) {
                return redirect()->back()->with('error', 'Bulk billing for this term has already been finalized.');
            }

            $batch = $this->billingService->billTerm(
                $request->session_id,
                $request->semester_id,
                Auth::id()
            );

            return redirect()->back()->with('success', "Bulk billing executed successfully. Billed {$batch->student_count} students totaling ₦" . number_format($batch->total_amount, 2));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error during bulk billing: ' . $e->getMessage());
        }
    }

    /**
     * AJAX Methods for Advanced UI
     */
    public function getFeesAjax(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:school_sessions,id',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        $fees = ClassFee::with('feeHead')
            ->where('class_id', $request->class_id)
            ->where('session_id', $request->session_id)
            ->where('semester_id', $request->semester_id)
            ->get();

        return response()->json([
            'success' => true,
            'fees' => $fees,
            'total' => $fees->sum('amount')
        ]);
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'fee_head_id' => 'required|exists:fee_heads,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'session_id' => 'required|exists:school_sessions,id',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        try {
            // Check for duplicates
            $exists = ClassFee::where('class_id', $request->class_id)
                ->where('fee_head_id', $request->fee_head_id)
                ->where('session_id', $request->session_id)
                ->where('semester_id', $request->semester_id)
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'This fee type is already assigned.'], 422);
            }

            $fee = ClassFee::create($request->all());
            $fee->load('feeHead');

            return response()->json([
                'success' => true,
                'message' => 'Fee added successfully.',
                'fee' => $fee
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyAjax($id)
    {
        try {
            $fee = ClassFee::findOrFail($id);
            $fee->delete();
            return response()->json(['success' => true, 'message' => 'Fee removed successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getBulkPreviewAjax(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:school_sessions,id',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        $classes = SchoolClass::with([
            'students' => function ($q) {
                $q->where('status', 'active');
            }
        ])->get();

        $previewData = [];
        $totalEstimated = 0;
        $totalStudents = 0;
        $incompleteClasses = 0;

        foreach ($classes as $class) {
            $fees = $class->getFeeItems($request->session_id, $request->semester_id);
            $studentCount = $class->students->count();
            $termTotal = $fees->sum('amount');
            $classEstimate = $termTotal * $studentCount;

            $previewData[] = [
                'class_name' => $class->class_name,
                'student_count' => $studentCount,
                'term_total' => $termTotal,
                'class_estimate' => $classEstimate,
                'is_ready' => $fees->count() > 0
            ];

            if ($fees->count() == 0)
                $incompleteClasses++;
            $totalEstimated += $classEstimate;
            $totalStudents += $studentCount;
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_students' => $totalStudents,
                'total_estimated' => $totalEstimated,
                'incomplete_count' => $incompleteClasses,
                'is_fully_ready' => $incompleteClasses == 0
            ],
            'details' => $previewData
        ]);
    }

    public function copyTermFeesAjax(Request $request)
    {
        $request->validate([
            'source_semester_id' => 'required|exists:semesters,id',
            'target_semester_id' => 'required|exists:semesters,id',
            'session_id' => 'required|exists:school_sessions,id',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id'
        ]);

        try {
            $classes = $request->class_ids ? SchoolClass::whereIn('id', $request->class_ids)->get() : SchoolClass::all();
            $copiedCount = 0;

            foreach ($classes as $class) {
                $sourceFees = ClassFee::where('class_id', $class->id)
                    ->where('semester_id', $request->source_semester_id)
                    ->get();

                foreach ($sourceFees as $sFee) {
                    $exists = ClassFee::where('class_id', $class->id)
                        ->where('semester_id', $request->target_semester_id)
                        ->where('fee_head_id', $sFee->fee_head_id)
                        ->exists();

                    if (!$exists) {
                        ClassFee::create([
                            'class_id' => $class->id,
                            'fee_head_id' => $sFee->fee_head_id,
                            'amount' => $sFee->amount,
                            'session_id' => $request->session_id,
                            'semester_id' => $request->target_semester_id,
                            'description' => $sFee->description . ' (Copied)'
                        ]);
                        $copiedCount++;
                    }
                }
            }

            return response()->json(['success' => true, 'message' => "Successfully copied {$copiedCount} fee definitions."]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
