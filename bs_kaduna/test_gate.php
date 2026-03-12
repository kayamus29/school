<?php
$user = \App\Models\User::role('Teacher')->first();
if (!$user) {
    echo "No teacher found.\n";
    exit;
}
\Illuminate\Support\Facades\Auth::login($user);

$check1 = \Illuminate\Support\Facades\Gate::check('view assigned students');
$check2 = \Illuminate\Support\Facades\Gate::check('view-student-list');

$check3 = \Illuminate\Support\Facades\Gate::check('view-attendance-pages');

$output = "User: " . $user->email . "\n";
$output .= "Role: " . $user->getRoleNames()->first() . "\n";
$output .= "Check 'view assigned students': " . ($check1 ? 'TRUE' : 'FALSE') . "\n";
$output .= "Check 'view-student-list': " . ($check2 ? 'TRUE' : 'FALSE') . "\n";
$output .= "Check 'view-attendance-pages': " . ($check3 ? 'TRUE' : 'FALSE') . "\n";

file_put_contents('C:\\Users\\kaygo\\Desktop\\Unifiedtransform\\gate_output.txt', $output);
echo "Done.\n";
