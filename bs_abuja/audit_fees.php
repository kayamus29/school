<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SchoolClass;
use App\Models\ClassFee;
use App\Models\SchoolSession;
use App\Models\Semester;

$classes = SchoolClass::all();
$sessions = SchoolSession::all();
$semesters = Semester::all();

echo "Class | Session | Term | Fee Items (Name:Amount) | Total Fee | Status\n";
echo "----------------------------------------------------------------------\n";

foreach ($classes as $c) {
    foreach ($sessions as $sess) {
        $termList = $semesters->where('session_id', $sess->id);
        foreach ($termList as $term) {
            $fees = ClassFee::where('class_id', $c->id)
                ->where('session_id', $sess->id)
                ->where('semester_id', $term->id)
                ->with('feeHead')
                ->get();

            echo $c->class_name . " | " . $sess->session_name . " | " . $term->semester_name . " | ";

            if ($fees->isEmpty()) {
                echo "N/A | 0.00 | MISSING\n";
            } else {
                $items = [];
                $total = 0;
                foreach ($fees as $f) {
                    $items[] = ($f->feeHead->name ?? 'Unknown') . ':' . $f->amount;
                    $total += $f->amount;
                }
                echo implode(', ', $items) . " | " . number_format($total, 2) . " | COMPLETE\n";
            }
        }
    }
}
