<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentFee;
use App\Models\StudentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WalletInvariantTest extends TestCase
{
    use DatabaseTransactions; // Rolls back changes after each test

    public function test_wallet_balance_equals_sum_of_transactions()
    {
        $student = User::where('role', 'student')->first();
        if (!$student)
            $this->markTestSkipped('No students found.');

        // Get Wallet Balance
        $wallet = DB::table('wallets')->where('student_id', $student->id)->first();
        $this->assertNotNull($wallet, "Wallet not created for student {$student->id}");

        // Get Sum of Txs
        $sum = DB::table('wallet_transactions')->where('wallet_id', $wallet->id)->sum('amount');

        // Check Invariant
        $this->assertEquals($wallet->balance, $sum, "Invariant Broken: Wallet Balance != Sum(Tx)");
    }

    public function test_deposit_increases_balance()
    {
        $student = User::where('role', 'student')->first();
        if (!$student)
            $this->markTestSkipped('No students found.');

        $service = app(\App\Interfaces\WalletServiceInterface::class);
        $initialBalance = $service->getBalance($student->id);
        $amount = 5000;

        $service->deposit($student->id, $amount, 'test', 1, 'Test Deposit');

        $newBalance = $service->getBalance($student->id);

        $this->assertEquals($initialBalance + $amount, $newBalance, "Deposit did not increase balance correctly.");
        $this->assertTrue($service->checkInvariant($student->id), "Invariant broken after deposit.");
    }

    public function test_charge_decreases_balance()
    {
        $student = User::where('role', 'student')->first();
        if (!$student)
            $this->markTestSkipped('No students found.');

        $service = app(\App\Interfaces\WalletServiceInterface::class);
        $initialBalance = $service->getBalance($student->id);
        $amount = 2000; // Charge amount (positive input, service handles sign)

        $service->charge($student->id, $amount, 'test', 2, 'Test Charge');

        $newBalance = $service->getBalance($student->id);

        // Balance should decrease by 2000
        $this->assertEquals($initialBalance - $amount, $newBalance, "Charge did not decrease balance correctly.");
        $this->assertTrue($service->checkInvariant($student->id), "Invariant broken after charge.");
    }

    public function test_global_ledger_integrity()
    {
        // Global Invariant: Sum(Wallets) == Sum(Payments) - Sum(Fees) + Sum(Transfers)

        $walletSum = DB::table('wallets')->sum('balance');

        $paymentSum = DB::table('student_payments')->sum('amount_paid');
        $feeSum = DB::table('student_fees')->sum('amount');

        // "Transfers" are tricky globally because we neutralized them.
        // We need to sum the 'transfer_credit' transactions.
        $transferCredits = DB::table('wallet_transactions')->where('type', 'transfer_credit')->sum('amount');

        $expected = $paymentSum - $feeSum + $transferCredits;

        // Floating point comparison
        $diff = abs($walletSum - $expected);

        $this->assertLessThan(1.00, $diff, "Global Invariant Broken: Wallets vs (Pay-Fee+Transfer). Diff: {$diff}");
    }
}
