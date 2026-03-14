<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\Mark;
use App\Models\User;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Ghost Mark (200) Detective ---\n";

// Get all marks, group by student and course
$allMarks = Mark::all();

$grouped = $allMarks->groupBy(function ($item) {
    return $item->student_id . '-' . $item->course_id . '-' . $item->exam->semester_id;
});

foreach ($grouped as $key => $marks) {
    $sum = $marks->sum('marks');

    if ($sum == 200) {
        echo "FOUND ANOMALY: $key -> Sum: $sum\n";
        echo "Breakdown:\n";
        foreach ($marks as $m) {
            echo "  - ID: {$m->id}, ExamID: {$m->exam_id}, Marks: {$m->marks}, Created: {$m->created_at}\n";
            echo "    Attributes: " . json_encode($m->getAttributes()) . "\n";
        }
    }
}
