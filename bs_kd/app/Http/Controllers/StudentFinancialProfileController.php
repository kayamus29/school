<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentFee;
use App\Models\StudentPayment;

class StudentFinancialProfileController extends Controller
{
    protected $walletService;

    public function __construct(\App\Interfaces\WalletServiceInterface $walletService)
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
        $this->walletService = $walletService;
    }

    public function show($id)
    {
        $student = User::with(['promotions.schoolClass', 'promotions.session'])->findOrFail($id);

        $fees = StudentFee::with(['feeHead', 'session', 'semester'])
            ->where('student_id', $id)
            ->latest()
            ->get();

        $payments = StudentPayment::with(['schoolClass', 'session', 'semester', 'receiver', 'studentFee.feeHead'])
            ->where('student_id', $id)
            ->latest()
            ->get();

        $walletBalance = $this->walletService->getBalance($id);

        return view('accounting.students.financial_profile', compact('student', 'fees', 'payments', 'walletBalance'));
    }
}
