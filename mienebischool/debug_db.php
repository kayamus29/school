<?php
try {
    $count = App\Models\StudentParentInfo::count();
    echo "Total Infos: $count\n";

    $emails = App\Models\StudentParentInfo::whereNotNull('guardian_email')
        ->where('guardian_email', '!=', '')
        ->pluck('guardian_email');

    echo "Emails found: " . $emails->count() . "\n";
    if ($emails->count() > 0) {
        $email = $emails->first();
        echo "Trying to create user for: $email\n";

        // Attempt create manually
        $user = App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => 'Test',
                'last_name' => 'Parent',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role' => 'Parent',
                'phone' => '1234567890', // Dummy
                'address' => 'Test Address',
                'nationality' => 'Test',
                'birthday' => '2000-01-01',
                'gender' => 'Other',
                'blood_type' => 'O+'
            ]
        );
        echo "User ID: " . $user->id . "\n";

        $info = App\Models\StudentParentInfo::where('guardian_email', $email)->first();
        $info->parent_user_id = $user->id;
        $info->save();
        echo "Linked successfully.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
