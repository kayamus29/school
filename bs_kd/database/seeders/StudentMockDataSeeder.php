<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentParentInfo;
use App\Models\StudentAcademicInfo;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class StudentMockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // 1. Get or Create Session
        $session = SchoolSession::first() ?: SchoolSession::create(['session_name' => '2025/2026']);

        // 2. Get or Create Class
        $class = SchoolClass::first() ?: SchoolClass::create(['class_name' => 'Grade 1', 'session_id' => $session->id]);

        // 3. Get or Create Section
        $section = Section::where('class_id', $class->id)->first() ?: Section::create([
            'section_name' => 'A',
            'class_id' => $class->id,
            'session_id' => $session->id,
            'room_no' => '101'
        ]);

        // 4. Create Students
        for ($i = 0; $i < 20; $i++) {
            DB::transaction(function () use ($faker, $session, $class, $section, $i) {
                $student = User::create([
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'gender' => $faker->randomElement(['Male', 'Female']),
                    'nationality' => 'Nigerian',
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'address2' => $faker->streetAddress,
                    'city' => $faker->city,
                    'zip' => $faker->postcode,
                    'birthday' => $faker->date(),
                    'blood_type' => $faker->randomElement(['A+', 'B+', 'O+', 'AB+']),
                    'religion' => $faker->randomElement(['Christianity', 'Islam']),
                ]);

                // Parent Info
                StudentParentInfo::create([
                    'student_id' => $student->id,
                    'father_name' => $faker->name('male'),
                    'father_phone' => $faker->phoneNumber,
                    'mother_name' => $faker->name('female'),
                    'mother_phone' => $faker->phoneNumber,
                    'guardian_email' => $faker->safeEmail,
                    'guardian_phone' => $faker->phoneNumber,
                    'parent_address' => $student->address,
                ]);

                // Academic Info
                StudentAcademicInfo::create([
                    'student_id' => $student->id,
                    'board_reg_no' => 'REG-' . $faker->unique()->randomNumber(5),
                ]);

                // Promotion (Linking student to class/session)
                Promotion::create([
                    'student_id' => $student->id,
                    'session_id' => $session->id,
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'id_card_number' => 'ID-' . (2025 + $i) . '-' . $faker->randomNumber(4),
                ]);
            });
        }
    }
}
