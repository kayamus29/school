<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\SchoolSession;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Diagnostic Start ---\n";

// 1. Check Session
$session = SchoolSession::latest()->first(); // Or relevant logic
echo "Latest Session ID: " . ($session ? $session->id : 'NULL') . "\n";
$sessionId = $session ? $session->id : 1; // Fallback for test

// 2. Role Counts
$countBigS = User::role('Student')->count();
echo "Users with role 'Student': $countBigS\n";

$countSmallS = User::role('student')->count();
echo "Users with role 'student': $countSmallS\n";

$countArray = User::role(['Student', 'student'])->count();
echo "Users with role ['Student', 'student']: $countArray\n";

// 3. Status
$activeCount = User::where('status', 'active')->count();
echo "Users with status 'active': $activeCount\n";

// 4. Promotions to Session
$promotedCount = User::whereHas('promotions', function ($q) use ($sessionId) {
    $q->where('session_id', $sessionId);
})->count();
echo "Users promoted to Session $sessionId: $promotedCount\n";

// 5. Combined Query (Replica of BillingService)
$eligible = User::role(['Student', 'student'])
    ->where('status', 'active')
    ->whereHas('promotions', function ($q) use ($sessionId) {
        $q->where('session_id', $sessionId);
    })
    ->count();

echo "--- FINAL ELIGIBLE COUNT (BillingService logic) ---: $eligible\n";

// 6. Sample User Check (if count is low)
if ($eligible < 5) {
    echo "\nSample Eligible Users:\n";
    $users = User::role(['Student', 'student'])
        ->where('status', 'active')
        ->whereHas('promotions', function ($q) use ($sessionId) {
            $q->where('session_id', $sessionId);
        })
        ->take(5)->get();
    foreach ($users as $u) {
        echo "- ID: {$u->id}, Name: {$u->first_name} {$u->last_name}, Role: {$u->role}\n";
    }
}
