<?php
require __DIR__ . '/vendor/autoload.php';
use App\Models\Exam;
use App\Models\Mark;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Bad Exam Cleanup ---\n";

$badExamIds = [1, 2];

foreach ($badExamIds as $id) {
    $exam = Exam::find($id);
    if ($exam) {
        $markCount = Mark::where('exam_id', $id)->count();
        echo "Deleting Exam ID $id (Name: '{$exam->name}') with $markCount associated marks...\n";

        Mark::where('exam_id', $id)->delete();
        $exam->delete();
        echo "Result: Deleted.\n";
    } else {
        echo "Exam ID $id not found (already deleted).\n";
    }
}

echo "Cleanup Complete. Please reload the results page.\n";
