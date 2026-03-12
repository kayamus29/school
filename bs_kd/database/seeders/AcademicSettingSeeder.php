<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicSetting;

class AcademicSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AcademicSetting::updateOrCreate(
            ['id' => 1],
            [
                'attendance_type' => 'section',
                'marks_submission_status' => 0,
                'default_exam_weight' => 70,
                'default_ca1_weight' => 30,
            ]
        );
    }
}
