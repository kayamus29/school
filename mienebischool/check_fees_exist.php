<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\StudentFee;
use App\Models\SchoolSession;
use App\Models\Semester;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Fee Existence Check ---\n";

$session = SchoolSession::latest()->first();
$sessionId = $session ? $session->id : 1;
// Assuming we are testing the first semester of the active session
$semester = Semester::where('session_id', $sessionId)->first();
$semesterId = $semester ? $semester->id : 1;

echo "Checking Session: {$session->session_name} (ID: $sessionId), Semester: " . ($semester ? $semester->semester_name : 'N/A') . " (ID: $semesterId)\n";

$feeCount = StudentFee::where('session_id', $sessionId)
    ->where('semester_id', $semesterId)
    ->count();

echo "Total StudentFee records found for this term: $feeCount\n";

if ($feeCount > 0) {
    echo "Sample Fees:\n";
    $fees = StudentFee::where('session_id', $sessionId)
        ->where('semester_id', $semesterId)
        ->take(5)
        ->get();
    foreach ($fees as $fee) {
        echo "- Student ID: {$fee->student_id}, Amount: {$fee->amount}, Status: {$fee->status}, Batch: {$fee->billing_batch_id}\n";
    }
} else {
    echo "No fees found. This suggests the BILLING LOOP IS NOT SKIPPING, but failing or not entering.\n";
}
