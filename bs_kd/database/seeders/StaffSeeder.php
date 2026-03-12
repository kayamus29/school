<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Ensure Site Settings exist with default geo values
        \App\Models\SiteSetting::updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Unified Transform Academy',
                'office_lat' => 6.5244,
                'office_long' => 3.3792,
                'geo_range' => 500,
                'late_time' => '08:00:00',
            ]
        );

        // 2. Create Staff Users
        $staffMembers = [
            [
                'first_name' => 'John',
                'last_name' => 'Librarian',
                'email' => 'librarian@example.com',
                'role' => 'librarian',
                'gender' => 'male',
                'nationality' => 'Nigerian',
                'phone' => '08012345678',
                'address' => '123 Library St',
                'address2' => 'Suite 1',
                'city' => 'Lagos',
                'zip' => '100001',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Staff',
                'email' => 'staff@example.com',
                'role' => 'staff',
                'gender' => 'female',
                'nationality' => 'Nigerian',
                'phone' => '08087654321',
                'address' => '456 Main Rd',
                'address2' => 'Block B',
                'city' => 'Lagos',
                'zip' => '100002',
            ],
        ];

        foreach ($staffMembers as $memberData) {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $memberData['email']],
                array_merge($memberData, [
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ])
            );

            // 3. Create mock attendance for the last 5 days
            for ($i = 1; $i <= 5; $i++) {
                $date = \Carbon\Carbon::today()->subDays($i);

                // Randomize arrival time (mostly on-time, some late)
                $onTime = rand(1, 100) > 20;
                $hour = $onTime ? rand(7, 7) : rand(8, 9);
                $minute = rand(0, 59);
                $checkInAt = (clone $date)->setTime($hour, $minute);

                $checkOutAt = (clone $checkInAt)->addHours(rand(7, 9))->addMinutes(rand(0, 59));

                \App\Models\StaffAttendance::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date->toDateString()],
                    [
                        'check_in_at' => $checkInAt,
                        'check_out_at' => $checkOutAt,
                        'check_in_lat' => 6.5244,
                        'check_in_long' => 3.3792,
                        'check_out_lat' => 6.5244,
                        'check_out_long' => 3.3792,
                        'status' => $onTime ? 'on-time' : 'late',
                    ]
                );
            }
        }
    }
}
