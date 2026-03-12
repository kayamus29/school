<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Core Permissions List
        $permissions = [
            'create users',
            'view users',
            'edit users',
            'delete users',
            'promote students',
            'create notices',
            'view notices',
            'edit notices',
            'delete notices',
            'create events',
            'view events',
            'edit events',
            'delete events',
            'create syllabi',
            'view syllabi',
            'edit syllabi',
            'delete syllabi',
            'create routines',
            'view routines',
            'edit routines',
            'delete routines',
            'create exams',
            'view exams',
            'delete exams',
            'create exams rule',
            'view exams rule',
            'edit exams rule',
            'delete exams rule',
            'view exams history',
            'create grading systems',
            'view grading systems',
            'edit grading systems',
            'delete grading systems',
            'create grading systems rule',
            'view grading systems rule',
            'edit grading systems rule',
            'delete grading systems rule',
            'take attendances',
            'view attendances',
            'update attendances type',
            'submit assignments',
            'create assignments',
            'view assignments',
            'save marks',
            'view marks',
            'create school sessions',
            'create semesters',
            'view semesters',
            'edit semesters',
            'assign teachers',
            'create courses',
            'view courses',
            'edit courses',
            'view academic settings',
            'update marks submission window',
            'update browse by session',
            'create classes',
            'view classes',
            'edit classes',
            'create sections',
            'view sections',
            'edit sections'
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
