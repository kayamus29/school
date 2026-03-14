<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\ClassFee;
use App\Models\StudentFee;
use App\Models\SchoolSession;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Billing Logic Simulation ---\n";

$sessionId = 1;
$semesterId = 1;

// 1. Fetch Class Fees
$classFees = ClassFee::where('session_id', $sessionId)
    ->where('semester_id', $semesterId)
    ->get(); // Load all fees into memory

echo "Loaded " . $classFees->count() . " global class fees.\n";

// 2. Fetch Students (Hybrid Query)
$students = User::where(function ($q) {
    $q->role(['Student', 'student'])
        ->orWhereIn('role', ['Student', 'student']);
})
    ->where('status', 'active')
    ->whereHas('promotions', function ($q) use ($sessionId) {
        $q->where('session_id', $sessionId);
    })
    ->with([
        'promotions' => function ($q) use ($sessionId) {
            $q->where('session_id', $sessionId);
        }
    ])
    ->get();

echo "Loaded " . $students->count() . " eligible students.\n\n";

foreach ($students as $student) {
    echo "Processing Student ID: {$student->id} ({$student->first_name})...\n";

    $promotion = $student->promotions->first();
    if (!$promotion) {
        echo "  [SKIP] No promotion found (unexpected).\n";
        continue;
    }

    $classId = $promotion->class_id;
    echo "  - Class ID: $classId\n";

    // Strictness check
    $studentFeesToApply = $classFees->where('class_id', $classId);

    if ($studentFeesToApply->isEmpty()) {
        echo "  [SKIP] No fees found for Class ID $classId in memory collection.\n";
        // Diagnostic for type mismatch
        echo "    Debug: Available ClassFee ClassIDs: " . implode(',', $classFees->pluck('class_id')->unique()->toArray()) . "\n";
        continue;
    }

    echo "  - Found " . $studentFeesToApply->count() . " fees to apply.\n";

    foreach ($studentFeesToApply as $feeTemplate) {
        echo "    - Checking Fee Head: {$feeTemplate->fee_head_id} (Amount: {$feeTemplate->amount})\n";

        $exists = StudentFee::where('student_id', $student->id)
            ->where('fee_head_id', $feeTemplate->fee_head_id)
            ->where('session_id', $sessionId)
            ->where('semester_id', $semesterId)
            ->exists();

        if ($exists) {
            echo "      [SKIP] Idempotency: Already exists.\n";
        } else {
            echo "      [ACTION] Would Create Fee Record here.\n";
        }
    }
    echo "\n";
}
