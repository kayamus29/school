<?php

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\Course;
use App\Models\Promotion;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\FeeHead;
use App\Models\ClassFee;
use App\Models\StudentPayment;
use App\Models\Expense;
use App\Models\Notice;
use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting Mock Data Injection..." . PHP_EOL;

// 1. Session
$currentSession = SchoolSession::where('session_name', '2025-2026')->first();
$sessionId = $currentSession->id;

// 2. Terms
$terms = ['First Term', 'Second Term', 'Third Term'];
foreach ($terms as $name) {
    Semester::firstOrCreate(['semester_name' => $name, 'session_id' => $sessionId]);
}
$semesterId = Semester::where('session_id', $sessionId)->first()->id;

// 3. Classes and Courses
$classes = SchoolClass::all();
$sections = Section::all();
$courseNames = ['Mathematics', 'English Language', 'Physics', 'Chemistry'];

foreach ($classes as $class) {
    foreach ($courseNames as $cName) {
        Course::firstOrCreate([
            'course_name' => $cName,
            'class_id' => $class->id,
            'session_id' => $sessionId,
            'semester_id' => $semesterId
        ], [
            'course_type' => 'Theory'
        ]);
    }
}
echo "Courses created." . PHP_EOL;

// 4. Students and Promotion
$students = User::role('Student')->get();
foreach ($students as $index => $student) {
    $class = $classes[$index % $classes->count()];
    $section = $sections->where('class_id', $class->id)->first() ?? $sections->first();

    Promotion::firstOrCreate(
        ['student_id' => $student->id, 'session_id' => $sessionId],
        ['class_id' => $class->id, 'section_id' => $section->id, 'id_card_number' => 'STD-' . str_pad($student->id, 5, '0', STR_PAD_LEFT)]
    );
}
echo "Students promoted." . PHP_EOL;

// 5. Attendance (Simulated)
echo "Generating Attendance..." . PHP_EOL;
for ($i = 0; $i < 5; $i++) {
    $date = Carbon::now()->subDays($i);
    foreach ($students->take(10) as $student) {
        $promo = Promotion::where('student_id', $student->id)->where('session_id', $sessionId)->first();
        if ($promo) {
            $course = Course::where('class_id', $promo->class_id)->first();
            if ($course) {
                Attendance::create([
                    'student_id' => $student->id,
                    'class_id' => $promo->class_id,
                    'section_id' => $promo->section_id,
                    'course_id' => $course->id,
                    'session_id' => $sessionId,
                    'status' => (rand(0, 10) > 2) ? 'Present' : 'Absent',
                    'created_at' => $date,
                    'updated_at' => $date
                ]);
            }
        }
    }
}

// 6. Exams and Marks
echo "Generating Marks..." . PHP_EOL;
foreach ($students->take(10) as $student) {
    $promo = Promotion::where('student_id', $student->id)->where('session_id', $sessionId)->first();
    if ($promo) {
        $course = Course::where('class_id', $promo->class_id)->first();
        if ($course) {
            $exam = Exam::firstOrCreate([
                'exam_name' => 'Continuous Assessment - ' . $course->course_name,
                'course_id' => $course->id,
                'session_id' => $sessionId
            ], [
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(7),
                'semester_id' => $semesterId,
                'class_id' => $promo->class_id
            ]);

            Mark::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'exam_id' => $exam->id,
                'session_id' => $sessionId,
                'class_id' => $promo->class_id,
                'section_id' => $promo->section_id,
                'marks' => rand(60, 90)
            ]);
        }
    }
}

// 7. Accounting
echo "Generating Accounting..." . PHP_EOL;
$heads = ['Tuition', 'Library'];
foreach ($heads as $h) {
    $head = FeeHead::firstOrCreate(['name' => $h]);
    foreach ($classes as $class) {
        ClassFee::firstOrCreate([
            'class_id' => $class->id,
            'fee_head_id' => $head->id,
            'session_id' => $sessionId,
            'semester_id' => $semesterId
        ], [
            'amount' => rand(1000, 5000),
            'description' => "Termly $h fee"
        ]);
    }
}

// 8. Notices
Notice::create([
    'notice' => 'Mock data generated successfully for testing.',
    'session_id' => $sessionId
]);

echo "All mock data injected successfully!" . PHP_EOL;
