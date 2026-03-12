<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSession;
use App\Models\StudentPayment;
use App\Models\ClassFee;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use App\Models\StudentFee;

class AccountingDashboardController extends Controller
{
    protected $walletService;

    public function __construct(\App\Interfaces\WalletServiceInterface $walletService)
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
        $this->walletService = $walletService;
    }

    public function index()
    {
        $session = SchoolSession::latest()->first();
        if (!$session) {
            return view('accounting.dashboard', ['error' => 'No academic session found.']);
        }

        // 1. Total Enrolled Students
        $totalStudents = User::where('role', 'student')->count();
        // Students with at least one transaction in current session tags (this is tricky with wallet, 
        // effectively we just show active students in the system)
        $activePayingStudents = User::where('role', 'student')->count();

        // 2. Revenue (Cashflow) = Sum of Payments
        // "Revenue = SUM(student_payments)"
        $totalReceived = StudentPayment::where('school_session_id', $session->id)->sum('amount_paid');

        // 3. Expected Revenue (Billed) = Sum of Fees
        // "Fees explain why... not authority" - still useful for "Expected" metric
        $totalExpectedFees = StudentFee::where('session_id', $session->id)->sum('amount');

        // 4. Wallet Statistics (Source of Truth)
        // Receivables = SUM(Negative Balances) => Debt
        // Liabilities = SUM(Positive Balances) => Credit (Prepaid)

        $totalReceivables = DB::table('wallets')->where('balance', '<', 0)->sum('balance'); // Returns negative sum, e.g -50000
        $totalLiabilities = DB::table('wallets')->where('balance', '>', 0)->sum('balance'); // Returns positive sum, e.g +20000

        // Display positive for dashboard readability? Usually reports show "Receivables: $50k" (meaning people owe us)
        // Let's pass the absolute value for "Receivables" display, or keep it negative to indicate "Asset/Debt"?
        // Standard accounting: Receivables is an Asset (+). 
        // But our wallet balance is Negative for debt.
        // Let's pass the raw sums and handle sign in View or here.
        // "Receivables = SUM(negative wallet balances)" -> This is strictly correct value-wise.

        // 5. Expenses
        $totalExpenses = Expense::sum('amount');

        // 6. Net Profit/Loss (Cash Basis) = Received - Expenses
        $netBalance = $totalReceived - $totalExpenses;

        // 7. Recent Transactions
        $recentPayments = StudentPayment::with(['student', 'schoolClass'])->latest()->take(5)->get();
        $recentExpenses = Expense::latest()->take(5)->get();

        return view('accounting.dashboard', compact(
            'totalStudents',
            'activePayingStudents',
            'totalExpectedFees',
            'totalReceived',
            'totalReceivables', // Debt
            'totalLiabilities', // Credit
            'totalExpenses',
            'netBalance',
            'recentPayments',
            'recentExpenses'
        ));
    }
    public function debtors(Request $request)
    {
        $search = $request->input('search');

        // Query students with negative wallet balance (meaning they owe money)
        $query = User::role('student')
            ->whereHas('wallet', function ($q) {
                $q->where('balance', '<', 0);
            })
            ->with([
                    'wallet',
                    'promotions' => function ($q) {
                        $q->latest()->take(1); // Get latest promotion for class info
                    },
                    'promotions.schoolClass'
                ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('promotions', function ($pq) use ($search) {
                        $pq->where('id_card_number', 'like', "%{$search}%");
                    });
            });
        }

        // Sort by biggest debt (lowest negative balance first)
        // Join wallet to sort? Or simple get then sort. Pagination makes simple sort hard.
        // Let's trying joining for sort.
        $query->join('wallets', 'users.id', '=', 'wallets.student_id')
            ->where('wallets.balance', '<', 0)
            ->select('users.*') // Avoid column collision
            ->orderBy('wallets.balance', 'asc'); // -5000 is smaller than -100, so ascending puts biggest debt first

        $debtors = $query->paginate(20)->appends(['search' => $search]);

        return view('accounting.debtors.index', compact('debtors'));
    }
}
