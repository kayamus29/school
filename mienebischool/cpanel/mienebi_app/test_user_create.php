<?php
// Quick test to see what's failing
use App\Models\StudentParentInfo;
use App\Models\User;

$info = StudentParentInfo::whereNotNull('guardian_email')->first();
if (!$info) {
    echo "No parent info found\n";
    exit;
}

echo "Email: {$info->guardian_email}\n";
echo "Attempting to create user...\n";

try {
    $user = User::create([
        'first_name' => 'Test',
        'last_name' => 'Parent',
        'email' => 'test_parent_' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'parent',
        'gender' => 'Other',
        'nationality' => 'Test',
        'phone' => '1234567890',
        'address' => 'Test',
        'city' => 'Test',
        'zip' => '12345',
    ]);
    echo "Success! User ID: {$user->id}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Full trace:\n";
    echo $e->getTraceAsString() . "\n";
}
