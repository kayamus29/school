<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset Cached Roles/Permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define Permissions
        $permissions = [
            'view accounting dashboard',
            'manage fee heads',
            'assign fees',
            'collect fees',
            'manage expenses',
            'view assigned classes',
            'take attendance',
            'manage marks',
            'save marks',
            'view marks',
            'create exams',
            'view exams',
            'view assigned syllabus',
            'staff check-in',
            'create expense transfer',
            'create expenses',
            'view own attendance',
            'view own marks',
            'view child records',
            'assign teachers',
            'edit classes',
            'edit sections',
            'edit courses',
            'view classes',
            'view audit logs',
            'view assigned students',
            'view users'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Create Roles & Sync Permissions
        $roles = [
            'Admin' => [],
            'Accountant' => ['view accounting dashboard', 'manage fee heads', 'assign fees', 'collect fees', 'manage expenses', 'staff check-in', 'create expenses'],
            'Teacher' => ['view assigned classes', 'take attendance', 'manage marks', 'save marks', 'view marks', 'create exams', 'view exams', 'view assigned syllabus', 'staff check-in', 'create expense transfer', 'view assigned students'],
            'Normal Staff' => ['staff check-in', 'create expense transfer'],
            'Student' => ['view own attendance', 'view own marks'],
            'Parent' => ['view child records'],
        ];

        foreach ($roles as $name => $perms) {
            $role = Role::firstOrCreate(['name' => $name]);
            if (!empty($perms)) {
                $role->syncPermissions($perms);
            }
        }

        // 4. Robust User Role Sync
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                if (!$user->role)
                    continue;

                $roleToAssign = match (strtolower($user->role)) {
                    'admin' => 'Admin',
                    'teacher' => 'Teacher',
                    'accountant' => 'Accountant',
                    'student' => 'Student',
                    'parent' => 'Parent',
                    'librarian', 'staff' => 'Normal Staff',
                    default => null
                };

                if ($roleToAssign && !$user->hasRole($roleToAssign)) {
                    $user->syncRoles($roleToAssign);
                }
            }
        });
    }
}
