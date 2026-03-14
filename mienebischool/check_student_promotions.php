<?php

// Script to check student promotion records
// Run this to diagnose why a student isn't appearing in marks entry

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Promotion;
use App\Models\SchoolSession;

echo "=== Student Promotion Diagnostic Tool ===\n\n";

// Get current session
$currentSession = SchoolSession::where('is_current', 1)->first();
if (!$currentSession) {
    $currentSession = SchoolSession::latest()->first();
}

echo "Current Session: " . ($currentSession ? $currentSession->session_name . " (ID: {$currentSession->id})" : "NONE") . "\n\n";

// Get all students
$students = User::where('role', 'student')->orderBy('created_at', 'desc')->take(10)->get();

echo "Recent Students (Last 10):\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s %-20s %-30s %-15s\n", "ID", "Name", "Email", "Created");
echo str_repeat("-", 80) . "\n";

foreach ($students as $student) {
    printf(
        "%-5s %-20s %-30s %-15s\n",
        $student->id,
        substr($student->first_name . ' ' . $student->last_name, 0, 20),
        substr($student->email, 0, 30),
        $student->created_at->format('Y-m-d H:i')
    );

    // Check promotion records
    $promotions = Promotion::where('student_id', $student->id)->get();

    if ($promotions->isEmpty()) {
        echo "  ⚠️  NO PROMOTION RECORDS FOUND!\n";
    } else {
        foreach ($promotions as $promo) {
            $session = SchoolSession::find($promo->session_id);
            $isCurrent = ($currentSession && $promo->session_id == $currentSession->id) ? "✓ CURRENT" : "  ";
            echo "  {$isCurrent} Session: {$session->session_name} | Class: {$promo->class_id} | Section: {$promo->section_id}\n";
        }
    }
    echo "\n";
}

echo "\n=== Summary ===\n";
$studentsWithoutPromotion = User::where('role', 'student')
    ->whereNotIn('id', Promotion::pluck('student_id'))
    ->count();

echo "Students without ANY promotion record: {$studentsWithoutPromotion}\n";

if ($currentSession) {
    $studentsWithoutCurrentPromotion = User::where('role', 'student')
        ->whereNotIn('id', Promotion::where('session_id', $currentSession->id)->pluck('student_id'))
        ->count();

    echo "Students without promotion in CURRENT session: {$studentsWithoutCurrentPromotion}\n";
}
