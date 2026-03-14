<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Unifiedtransform',
                'primary_color' => '#3490dc',
                'secondary_color' => '#ffffff',
                'geo_range' => 500,
                'late_time' => '08:00',
            ]
        );
    }
}
