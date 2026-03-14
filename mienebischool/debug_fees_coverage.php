<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Promotion;
use App\Models\ClassFee;
use App\Models\SchoolSession;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Fee Coverage Diagnostic ---\n";

$sessionId = 1; // Assuming Session 1 as per previous output
$semesterId = 1; // Assuming Term 1

$promotions = Promotion::where('session_id', $sessionId)->get();
echo "Total Promoted Students: " . $promotions->count() . "\n";

$referencedClassIds = $promotions->pluck('class_id')->unique();
echo "Classes represented: " . implode(', ', $referencedClassIds->toArray()) . "\n";

echo "\n--- Checking Fee Definitions for these Classes ---\n";

foreach ($referencedClassIds as $classId) {
    $feeCount = ClassFee::where('session_id', $sessionId)
        ->where('semester_id', $semesterId)
        ->where('class_id', $classId)
        ->count();

    echo "Class ID $classId: Found $feeCount fee definitions.\n";
}

echo "\n--- Student Breakdown ---\n";
foreach ($promotions as $p) {
    echo "Student ID {$p->student_id} is in Class ID {$p->class_id}. ";
    $fees = ClassFee::where('session_id', $sessionId)
        ->where('semester_id', $semesterId)
        ->where('class_id', $p->class_id)
        ->count();

    if ($fees == 0) {
        echo "[WARNING] NO FEES DEFINED -> Will skip.\n";
    } else {
        echo "[OK] Has $fees fees defined -> Should process.\n";
    }
}
