<?php
// Direct database seeding for parent users
use App\Models\StudentParentInfo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    // Get all unique guardian emails
    $infos = StudentParentInfo::whereNotNull('guardian_email')
        ->where('guardian_email', '!=', '')
        ->get();

    $grouped = $infos->groupBy('guardian_email');

    echo "Found " . $grouped->count() . " unique guardian emails\n\n";

    $created = 0;
    $linked = 0;

    foreach ($grouped as $email => $records) {
        echo "Processing: $email (" . $records->count() . " children)\n";

        // Check if user already exists
        $existing = User::where('email', $email)->first();

        if ($existing) {
            echo "  User already exists (ID: {$existing->id})\n";
            $parentUser = $existing;
        } else {
            // Get name from first record
            $parentName = $records->first()->father_name ?: ($records->first()->mother_name ?: 'Parent');
            $parts = explode(' ', trim($parentName), 2);

            // Insert directly into database
            $userId = DB::table('users')->insertGetId([
                'first_name' => $parts[0],
                'last_name' => $parts[1] ?? 'Guardian',
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'parent',
                'gender' => 'Other',
                'nationality' => 'N/A',
                'phone' => $records->first()->guardian_phone ?? 'N/A',
                'address' => $records->first()->parent_address ?? 'N/A',
                'city' => 'N/A',
                'zip' => 'N/A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $parentUser = User::find($userId);
            $parentUser->assignRole('Parent');

            echo "  Created user (ID: $userId)\n";
            $created++;
        }

        // Link children
        foreach ($records as $record) {
            if ($record->parent_user_id !== $parentUser->id) {
                $record->parent_user_id = $parentUser->id;
                $record->save();
                $linked++;
            }
        }

        echo "  Linked " . $records->count() . " children\n\n";
    }

    DB::commit();

    echo "\n========================================\n";
    echo "SUCCESS!\n";
    echo "========================================\n";
    echo "Parents created: $created\n";
    echo "Students linked: $linked\n";
    echo "========================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
