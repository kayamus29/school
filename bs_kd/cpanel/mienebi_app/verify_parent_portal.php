<?php

use App\Models\User;
use App\Models\StudentParentInfo;
use App\Http\Controllers\ParentPortalController;
use Illuminate\Support\Facades\Auth;

// 1. Find a parent with children
$info = StudentParentInfo::whereNotNull('parent_user_id')->first();

if (!$info) {
    echo "No linked parents found. Please ensure sync ran.\n";
    exit(1);
}

$parentId = $info->parent_user_id;
$childId = $info->student_id;
$parentUser = User::find($parentId);
// Find a random other student ID for negative testing
$otherStudent = User::where('role', 'Student')->where('id', '!=', $childId)->first();

echo "Testing with Parent ID: $parentId, Child ID: $childId\n";
echo "Negative Test Child ID: " . $otherStudent->id . "\n";

// Login
Auth::login($parentUser);

$controller = new ParentPortalController();

// Test Dashboard
try {
    $response = $controller->dashboard();
    echo "[PASS] Dashboard accessed. Type: " . get_class($response) . "\n";
} catch (\Exception $e) {
    echo "[FAIL] Dashboard access failed: " . $e->getMessage() . "\n";
}

// Test Child Dashboard (Valid)
try {
    $response = $controller->childDashboard($childId);
    echo "[PASS] Valid Child Dashboard accessed. Type: " . get_class($response) . "\n";
} catch (\Exception $e) {
    echo "[FAIL] Valid Child Dashboard failed: " . $e->getMessage() . "\n";
}

// Test Child Dashboard (Invalid)
try {
    $controller->childDashboard($otherStudent->id);
    echo "[FAIL] Invalid Child Dashboard DID NOT throw exception!\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() === 403) {
        echo "[PASS] Invalid Child Dashboard rejected with 403.\n";
    } else {
        echo "[FAIL] Invalid Child Dashboard threw wrong code: " . $e->getStatusCode() . "\n";
    }
} catch (\Exception $e) {
    echo "[FAIL] Invalid Child Dashboard threw generic exception: " . $e->getMessage() . "\n";
}
