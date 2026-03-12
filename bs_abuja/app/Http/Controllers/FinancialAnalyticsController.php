<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentFee;
use App\Models\StudentPayment;
use App\Models\SchoolSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
    }

    public function index(Request $request)
    {
        $session_id = $request->get('session_id', SchoolSession::latest()->first()->id ?? null);
        $sessions = SchoolSession::all();

        // 1. Revenue Summaries
        $totalRevenue = StudentPayment::where('school_session_id', $session_id)->sum('amount_paid');
        $totalOutstanding = StudentFee::where('session_id', $session_id)->sum('balance');
        $totalExpected = StudentFee::where('session_id', $session_id)->sum('amount');

        // 2. Growth Analytics (Month-on-Month for the current year)
        $currentYear = date('Y');
        $monthlyRevenue = StudentPayment::select(
            DB::raw('MONTH(transaction_date) as month'),
            DB::raw('SUM(amount_paid) as total')
        )
            ->whereYear('transaction_date', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Fill missing months with 0
        $growthData = [];
        for ($m = 1; $m <= 12; $m++) {
            $growthData[] = floatval($monthlyRevenue[$m] ?? 0);
        }

        // 3. Fee Type Breakdown (Tuition vs Addons)
        $feeTypes = StudentFee::select('fee_type', DB::raw('SUM(amount_paid) as revenue'))
            ->where('session_id', $session_id)
            ->groupBy('fee_type')
            ->get();

        // 4. Top Revenue Classes (Optional/Nice to have)
        $classRevenue = StudentPayment::with('schoolClass')
            ->select('class_id', DB::raw('SUM(amount_paid) as total'))
            ->where('school_session_id', $session_id)
            ->groupBy('class_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('accounting.analytics.index', compact(
            'totalRevenue',
            'totalOutstanding',
            'totalExpected',
            'growthData',
            'feeTypes',
            'classRevenue',
            'sessions',
            'session_id'
        ));
    }
}
