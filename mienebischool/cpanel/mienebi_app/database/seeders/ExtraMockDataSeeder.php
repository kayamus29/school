<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Exam;
use App\Models\Attendance;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ExtraMockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // 1. Create Teachers
        for ($i = 0; $i < 5; $i++) {
            User::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'gender' => $faker->randomElement(['Male', 'Female']),
                'nationality' => 'Nigerian',
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'address2' => $faker->streetAddress,
                'city' => $faker->city,
                'zip' => $faker->postcode,
            ]);
        }

        // 2. Create Exams
        $session = SchoolSession::latest()->first();
        if ($session) {
            $semester = Semester::where('session_id', $session->id)->first() ?: Semester::create([
                'semester_name' => 'First Term',
                'session_id' => $session->id,
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ]);

            $class = SchoolClass::where('session_id', $session->id)->first() ?: SchoolClass::create([
                'class_name' => 'Grade 1',
                'session_id' => $session->id
            ]);

            $course = Course::where('class_id', $class->id)->first() ?: Course::create([
                'course_name' => 'Mathematics',
                'course_type' => 'Core',
                'class_id' => $class->id,
                'semester_id' => $semester->id,
                'session_id' => $session->id,
            ]);

            Exam::create([
                'exam_name' => 'First Term Examination',
                'session_id' => $session->id,
                'semester_id' => $semester->id,
                'class_id' => $class->id,
                'course_id' => $course->id,
                'start_date' => now()->addMonth(),
                'end_date' => now()->addMonth()->addDays(14),
            ]);
        }

        // 3. Create Student Attendance for Today (to show on dashboard)
        $today = now()->startOfDay();
        $students = User::where('role', 'student')->limit(30)->get();
        if ($students->isNotEmpty()) {
            $class = SchoolClass::first();
            $section = Section::first();

            foreach ($students as $index => $student) {
                // Mark some as Present, some as Absent
                $status = ($index % 8 == 0) ? 'Absent' : 'Present';

                Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'session_id' => $session ? $session->id : 0,
                        'created_at' => $today,
                    ],
                    [
                        'course_id' => $course->id ?? 0,
                        'class_id' => $class->id ?? 0,
                        'section_id' => $section->id ?? 0,
                        'status' => $status,
                    ]
                );
            }
        }
    }
}
