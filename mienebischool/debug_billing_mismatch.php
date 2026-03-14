<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Promotion;
use App\Models\SchoolSession;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Billing Mismatch Diagnostic ---\n";

$sessionId = 1; // Assuming Session 1 as per previous output
$promotions = Promotion::where('session_id', $sessionId)->get();
echo "Total Promotions found for Session $sessionId: " . $promotions->count() . "\n";

if ($promotions->isEmpty()) {
    echo "No promotions found. Preview should be 0.\n";
    exit;
}

$studentIds = $promotions->pluck('student_id')->unique();
echo "Unique Student IDs in Promotions: " . $studentIds->count() . "\n";

// Analyze these students
$users = User::whereIn('id', $studentIds)->get();
echo "Users found from these IDs: " . $users->count() . "\n";

echo "\n--- Status Distribution ---\n";
$statusCounts = $users->groupBy('status')->map->count();
print_r($statusCounts->toArray());

echo "\n--- Legacy Role Column Distribution ---\n";
$roleCounts = $users->groupBy('role')->map->count();
print_r($roleCounts->toArray());

echo "\n--- Spatie Role Check ---\n";
$spatieStudentCount = 0;
$legacyStudentCount = 0;
$activeCount = 0;

foreach ($users as $u) {
    $isSpatie = $u->hasRole(['Student', 'student']);
    $isLegacy = in_array($u->role, ['Student', 'student']);
    $isActive = ($u->status === 'active');

    if ($isSpatie)
        $spatieStudentCount++;
    if ($isLegacy)
        $legacyStudentCount++;
    if ($isActive)
        $activeCount++;
}

echo "Users with Spatie 'Student'/'student': $spatieStudentCount\n";
echo "Users with Legacy 'Student'/'student': $legacyStudentCount\n";
echo "Users with Status 'active': $activeCount\n";

// Simulate BillingService Query
$billingEligible = $users->filter(function ($u) {
    $hasRole = $u->hasRole(['Student', 'student']) || in_array($u->role, ['Student', 'student']);
    $isActive = ($u->status === 'active');
    return $hasRole && $isActive;
});

echo "\n--- FINAL ELIGIBLE COUNT (BillingService logic) ---: " . $billingEligible->count() . "\n";

if ($billingEligible->count() < $users->count()) {
    echo "\n!!! MATCH FOUND: The filters are excluding " . ($users->count() - $billingEligible->count()) . " promoted students.\n";
    if ($activeCount < $users->count())
        echo "- Status 'active' check is excluding " . ($users->count() - $activeCount) . " users.\n";
    if ($legacyStudentCount == 0 && $spatieStudentCount == 0)
        echo "- Role check is excluding EVERYONE.\n";
}
