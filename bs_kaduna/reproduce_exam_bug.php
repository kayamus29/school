<?php

use App\Repositories\ExamRepository;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Course;
use App\Models\Exam;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate parameters from the user request
// ?class_id=1&semester_id=1
$class_id = 1;
$semester_id = 1;
$current_school_session_id = 1; // Assuming 1 for now

// Test Course ID (assuming 1 exists and has exams)
$test_course_id = 1;

echo "--- Debugging Exam Repository ---\n";

$repo = new ExamRepository();
// PASS course_id here to test the fix!
$exams = $repo->getAll($current_school_session_id, $semester_id, $class_id, $test_course_id);

echo "Total Exams Found for Class $class_id: " . $exams->count() . "\n";

foreach ($exams as $exam) {
    echo "Exam ID: " . $exam->id . " | Name: " . $exam->exam_name . " | Course ID: " . $exam->course_id . "\n";
}

if ($exams->unique('course_id')->count() > 1) {
    echo "\n[FAIL] Multiple courses' exams returned! Isolation broken.\n";
} else {
    echo "\n[PASS] Only exams for a single course returned.\n";
}
