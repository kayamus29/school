<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__."/bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$activeStudents = User::where('status', 'active')->where('role', 'student')->get();

echo "Found " . $activeStudents->count() . " active students:\n";

foreach ($activeStudents as $student) {
    echo " - " . $student->email . "\n";
}

