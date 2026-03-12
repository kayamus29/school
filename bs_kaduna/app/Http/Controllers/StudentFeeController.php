<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentFee;
use App\Models\User;
use App\Models\FeeHead;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Traits\SchoolSession as SchoolSessionTrait;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\WalletServiceInterface;
use Illuminate\Validation\Rule;

class StudentFeeController extends Controller
{
    use SchoolSessionTrait;

    protected $schoolSessionRepository;
    protected $walletService;

    public function __construct(SchoolSessionInterface $schoolSessionRepository, WalletServiceInterface $walletService)
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->walletService = $walletService;
    }

    public function index()
    {
        $current_session_id = $this->getSchoolCurrentSession();

        $studentFees = StudentFee::with(['student', 'transaction', 'feeHead', 'session', 'semester'])
            ->where('session_id', $current_session_id)
            ->latest()
            ->paginate(15);

        $students = User::where('role', 'student')->get(['id', 'first_name', 'last_name']);
        $feeHeads = FeeHead::all();
        $sessions = SchoolSession::all();
        $semesters = Semester::where('session_id', $current_session_id)->get();

        return view('accounting.fees.student.index', compact('studentFees', 'students', 'feeHeads', 'sessions', 'semesters', 'current_session_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'student');
                }),
            ],
            'fee_head_id' => 'required|exists:fee_heads,id',
            'amount' => 'required|numeric|min:0|max:10000000',
            'description' => 'nullable|string|max:255',
            'session_id' => 'required|exists:school_sessions,id',
            'semester_id' => [
                'required',
                Rule::exists('semesters', 'id')->where(function ($query) use ($request) {
                    $query->where('session_id', $request->session_id);
                }),
            ],
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['balance'] = $request->amount;
            $data['status'] = 'unpaid';
            $data['amount_paid'] = 0;
            $data['fee_type'] = 'addon'; // Explicitly mark as addon

            $fee = StudentFee::create($data);

            // CORE CHANGE: Charge the wallet via Service
            // This is an Invoice/Charge.
            $this->walletService->charge(
                $request->student_id,
                $request->amount,
                'student_fee', // Fixed: Use short name to match system convention
                $fee->id,
                $request->description ?? 'Fee Charge'
            );

            // The wallet has been charged. The StudentFee record remains as 'unpaid'
            // to serve as the invoice for the debt now tracked in the wallet.
            // DO NOT mark it as paid here.

            // Refresh wallet balance for feedback
            $newBalance = $this->walletService->getBalance($request->student_id);
            $formattedBalance = number_format($newBalance, 2);

            DB::commit();

            return redirect()->back()->with('success', "Fee assigned and paid via Wallet. Wallet Charged: ₦" . number_format($request->amount, 2) . ". New Details: Balance ₦{$formattedBalance}.");
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error assigning fee: ' . $e->getMessage());
        }
    }

    public function getOutstanding($student_id)
    {
        $fees = StudentFee::with(['feeHead', 'session', 'semester'])
            ->where('student_id', $student_id)
            ->where('balance', '>', 0)
            ->get();

        return response()->json($fees);
    }

    public function destroy($id)
    {
        try {
            $fee = StudentFee::findOrFail($id);
            $fee->delete();
            return redirect()->back()->with('success', 'Student fee removed successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error removing fee: ' . $e->getMessage());
        }
    }
}
