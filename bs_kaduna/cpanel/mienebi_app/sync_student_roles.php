<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

$students = User::where('role', 'student')->get();
echo "Total Students in database (column 'role'): " . $students->count() . "\n";

$noRole = 0;
foreach ($students as $student) {
    if (!$student->hasRole('Student')) {
        $noRole++;
        $student->assignRole('Student');
    }
}

echo "Students that were missing the 'Student' role and have now been synced: $noRole\n";
echo "All students now have the 'Student' role.\n";
