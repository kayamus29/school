<?php
require __DIR__ . '/vendor/autoload.php';
use App\Models\Exam;
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Exam Identity Check ---\n";
$exams = Exam::whereIn('id', [1, 2])->get();

foreach ($exams as $e) {
    echo "ID: {$e->id}\n";
    echo "Name: {$e->name}\n";
    echo "Semester ID: {$e->semester_id}\n";
    echo "Session ID: {$e->session_id}\n";
    echo "Start Date: {$e->start_date}\n";
    echo "-------------------\n";
}
