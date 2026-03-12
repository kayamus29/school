<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Interfaces\WalletServiceInterface;
use Illuminate\Support\Facades\DB;

$service = app(WalletServiceInterface::class);
echo "Wallet Service Resolved: " . get_class($service) . "\n";

// Test 1: Invariant Check (Global)
echo "Running Global Invariant Check...\n";
$walletSum = DB::table('wallets')->sum('balance');
$expected = DB::table('student_payments')->sum('amount_paid')
    - DB::table('student_fees')->sum('amount')
    + DB::table('wallet_transactions')->where('type', 'transfer_credit')->sum('amount');

if (abs($walletSum - $expected) > 1.00) {
    echo "FAIL: Global Invariant Broken. Wallets: $walletSum, Expected: $expected\n";
    exit(1);
}
echo "PASS: Global Invariant Holds.\n";


// Test 2: Deposit Functional Test
echo "Running Deposit Test...\n";
DB::beginTransaction();
try {
    $student = User::where('role', 'student')->first();
    if (!$student)
        die("No students found.\n");

    $initialBalance = $service->getBalance($student->id);
    $amount = 5000;

    $service->deposit($student->id, $amount, 'test', 1, 'Ref');

    $newBalance = $service->getBalance($student->id);
    if (abs(($initialBalance + $amount) - $newBalance) > 0.01) {
        echo "FAIL: Deposit Logic. Expected " . ($initialBalance + $amount) . ", Got $newBalance\n";
        exit(1);
    }

    if (!$service->checkInvariant($student->id)) {
        echo "FAIL: Student Invariant Broken after deposit.\n";
        exit(1);
    }

    echo "PASS: Deposit Logic works.\n";

} catch (\Exception $e) {
    echo "FAIL: Exception during Deposit test: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    DB::rollBack(); // Always rollback test data
}

// Test 3: Charge Functional Test
echo "Running Charge Test...\n";
DB::beginTransaction();
try {
    $student = User::where('role', 'student')->first();

    $initialBalance = $service->getBalance($student->id);
    $amount = 2000;

    $service->charge($student->id, $amount, 'test', 2, 'Ref Charge');

    $newBalance = $service->getBalance($student->id);
    if (abs(($initialBalance - $amount) - $newBalance) > 0.01) {
        echo "FAIL: Charge Logic. Expected " . ($initialBalance - $amount) . ", Got $newBalance\n";
        exit(1);
    }

    if (!$service->checkInvariant($student->id)) {
        echo "FAIL: Student Invariant Broken after charge.\n";
        exit(1);
    }

    echo "PASS: Charge Logic works.\n";

} catch (\Exception $e) {
    echo "FAIL: Exception during Charge test: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    DB::rollBack();
}

echo "ALL TESTS PASSED.\n";
