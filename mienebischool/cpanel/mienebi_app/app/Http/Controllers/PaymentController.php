<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentPayment;
use App\Models\User;
use App\Models\StudentFee;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Semester;
use Exception;
use App\Interfaces\WalletServiceInterface;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $walletService;

    public function __construct(WalletServiceInterface $walletService)
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = StudentPayment::with(['student', 'schoolClass', 'session', 'semester', 'transaction']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('student.promotions', function ($pq) use ($search) {
                        $pq->where('id_card_number', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->latest('transaction_date')
            ->paginate(20)
            ->appends(['search' => $search]);

        return view('accounting.payments.index', compact('payments'));
    }

    public function create()
    {
        $students = User::where('role', 'student')->get(['id', 'first_name', 'last_name']);
        $classes = SchoolClass::all();
        $sessions = SchoolSession::all();
        $semesters = Semester::all();

        $student_id = request('student_id');
        $fees = [];
        if ($student_id) {
            $fees = StudentFee::where('student_id', $student_id)->where('balance', '>', 0)->get();
        }

        return view('accounting.payments.create', compact('students', 'classes', 'sessions', 'semesters', 'fees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:school_classes,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string',
            'school_session_id' => 'required|exists:school_sessions,id',
            'semester_id' => 'required|exists:semesters,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $ref = $request->reference_no ?? 'PAY-' . strtoupper(uniqid());

            // Create Payment Record
            $payment = StudentPayment::create([
                'student_id' => $request->student_id,
                'student_fee_id' => null, // Always null for strict wallet system
                'class_id' => $request->class_id,
                'school_session_id' => $request->school_session_id,
                'semester_id' => $request->semester_id,
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
                'transaction_date' => $request->transaction_date,
                'reference_no' => $ref,
                'received_by' => auth()->id(),
                'description' => $request->remarks,
            ]);

            // Wallet Deposit
            $description = "Payment " . $ref;
            if ($request->remarks) {
                $description .= " - " . $request->remarks;
            } else {
                $description .= " (Wallet Deposit)";
            }

            $this->walletService->deposit(
                $request->student_id,
                $request->amount_paid,
                'App\Models\StudentPayment',
                $payment->id,
                $description
            );

            // No legacy fee update - strict wallet system.

            DB::commit();

            return redirect()->route('accounting.payments.index')->with('success', 'Payment recorded successfully. Ref: ' . $payment->reference_no);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error recording payment: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $payment = StudentPayment::with(['student', 'schoolClass', 'session', 'semester'])->findOrFail($id);
        return view('accounting.payments.show', compact('payment'));
    }

    public function getStudentDetails($id)
    {
        // Get current session
        $session = SchoolSession::latest()->first(); // Or use the trait methods if available
        if (!$session) {
            return response()->json(['error' => 'No active session found'], 404);
        }

        $student = User::with([
            'promotions' => function ($q) use ($session) {
                $q->where('session_id', $session->id)->with('schoolClass', 'section');
            }
        ])->find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $promotion = $student->promotions->first();

        return response()->json([
            'class_id' => $promotion ? $promotion->class_id : null,
            'class_name' => $promotion ? $promotion->schoolClass->class_name : null,
            'session_id' => $session->id,
            'session_name' => $session->session_name, // Assuming column name
            // 'section_id' => $promotion ? $promotion->section_id : null, 
        ]);
    }
}
