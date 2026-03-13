<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__."/bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "Starting student reactivation process...\n";

try {
    $deactivatedStudents = User::where('status', 'deactivated')
        ->where('deactivation_reason', 'Not in the new student list for bulk synchronization.')
        ->get();

    $reactivatedCount = 0;
    foreach ($deactivatedStudents as $user) {
        $user->status = 'active';
        $user->deactivated_at = null;
        $user->deactivation_reason = null;
        $user->save();
        echo "  [SUCCESS] Reactivated student: {$user->email}\n";
        Log::info("Reactivated student: {$user->email}");
        $reactivatedCount++;
    }
    echo "\nReactivation process finished. {$reactivatedCount} students reactivated.\n";
} catch (\Exception $e) {
    echo "  [ERROR] Failed during reactivation process: " . $e->getMessage() . "\n";
    Log::error("Failed during reactivation process: " . $e->getMessage());
}


