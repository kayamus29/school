<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = User::where('role', 'student')->get();
$errors = [];

$log = "Starting Validation Simulation...\n";

foreach ($students as $student) {
    if ($student->id == 1) { // Skip default admin if role is student
        // continue; 
    }

    $runningBalance = 0;

    // A. Process Payments (Deposits)
    $payments = DB::table('student_payments')
        ->where('student_id', $student->id)
        ->orderBy('created_at')
        ->get();

    foreach ($payments as $payment) {
        $runningBalance += $payment->amount_paid;
    }

    // B. Process Fees (Charges)
    $fees = DB::table('student_fees')
        ->where('student_id', $student->id)
        ->orderBy('created_at')
        ->get();

    foreach ($fees as $fee) {
        $chargeAmount = -1 * abs($fee->amount);
        $runningBalance += $chargeAmount;

        if ($fee->transferred_to_id) {
            $creditAmount = $fee->amount - $fee->amount_paid;
            if ($creditAmount > 0) {
                $runningBalance += $creditAmount;
            }
        }
    }

    // C. Verify
    $legacyOutstanding = DB::table('student_fees')
        ->where('student_id', $student->id)
        ->whereNull('transferred_to_id')
        ->sum('balance');

    $expectedWalletBalance = -1 * $legacyOutstanding;

    if (abs($runningBalance - $expectedWalletBalance) > 1.00) {
        $log .= "Mismatch for Student ID {$student->id} ({$student->first_name}):\n";
        $log .= "  - Calculated Wallet: {$runningBalance}\n";
        $log .= "  - Legacy Outstanding (Active Debt): {$legacyOutstanding} -> Expected Wallet: {$expectedWalletBalance}\n";
        $log .= "  - Diff: " . ($runningBalance - $expectedWalletBalance) . "\n";

        // Debug details with detailed fee breakdown
        $log .= "  Details:\n";
        $log .= "  - Payments: " . $payments->sum('amount_paid') . "\n";
        $log .= "  - Fees Total: " . $fees->sum('amount') . "\n";
        $creditTotal = $fees->whereNotNull('transferred_to_id')->sum(function ($f) {
            return $f->amount - $f->amount_paid; });
        $log .= "  - Transferred Credit: " . $creditTotal . "\n";

        // Check if expected formula holds:
        // Expected = Payments - Fees + Credits
        $formulaCheck = $payments->sum('amount_paid') - $fees->sum('amount') + $creditTotal;
        $log .= "  - Formula Check (Pay - Bill + Credit): " . $formulaCheck . "\n";
        $log .= "------------------------------------------------\n";
    }
}

$log .= "Validation Complete.\n";
file_put_contents('migration_log.txt', $log);
