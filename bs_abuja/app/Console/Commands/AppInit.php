<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\AcademicSetting;
use Spatie\Permission\Models\Role;

class AppInit extends Command
{
    protected $signature = 'app:init';
    protected $description = 'Zero-assumption canonical initialization for the Unifiedtransform platform';

    public function handle()
    {
        $this->info('🚀 Starting Unifiedtransform Initialization...');

        // 0. Ensure Storage Directories Exist
        $this->ensureStorageDirectories();

        // 1. Wait for Database
        if (!$this->waitForDatabase()) {
            return 1;
        }

        // 2. Run Migrations
        $this->info('🏗️ Running Migrations...');
        Artisan::call('migrate', ['--force' => true], $this->getOutput());

        // 3. Clear Caches before Seeding (to ensure fresh state)
        $this->info('🧹 Clearing Caches...');
        Artisan::call('config:clear', [], $this->getOutput());
        Artisan::call('cache:clear', [], $this->getOutput());

        // 4. Run Core Seeders
        $this->info('🌱 Running Core Seeders...');
        Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true], $this->getOutput());
        Artisan::call('db:seed', ['--class' => 'AcademicSettingSeeder', '--force' => true], $this->getOutput());
        Artisan::call('db:seed', ['--class' => 'SiteSettingSeeder', '--force' => true], $this->getOutput());

        // 5. Enforce System Invariants
        $this->enforceInvariants();

        // 6. Generate Key if missing
        if (!config('app.key')) {
            $this->info('🔑 Generating Application Key...');
            Artisan::call('key:generate', ['--force' => true], $this->getOutput());
        }

        // 7. Final Cache Rebuild
        $this->info('⚡ Optimizing Application...');
        Artisan::call('config:cache', [], $this->getOutput());
        Artisan::call('view:cache', [], $this->getOutput());
        Artisan::call('permission:cache-reset', [], $this->getOutput());

        $this->info('✅ Initialization Complete!');
        return 0;
    }

    protected function waitForDatabase()
    {
        $maxAttempts = 30;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            try {
                DB::connection()->getPdo();
                $this->info('📦 Database Connected.');
                return true;
            } catch (\Exception $e) {
                $attempts++;
                $this->warn("⏳ Waiting for database... (Attempt $attempts/$maxAttempts)");
                sleep(2);
            }
        }

        $this->error('❌ Could not connect to the database.');
        return false;
    }

    protected function enforceInvariants()
    {
        $this->info('🛡️ Enforcing System Invariants...');

        // Ensure Admin Role exists (canonical casing via RoleSeeder, but double check)
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error('Critical Error: Admin role still missing after seeding.');
            return;
        }

        // Ensure Admin User exists
        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@ut.com');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => bcrypt($adminPassword),
                'role' => 'Admin' // Legacy compatibility
            ]
        );

        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
            $this->info("👮 Admin role assigned to $adminEmail.");
        }

        // Ensure AcademicSetting exists
        if (AcademicSetting::count() === 0) {
            AcademicSetting::create([
                'attendance_type' => 'section',
                'marks_submission_status' => 0,
                'default_exam_weight' => 70,
                'default_ca1_weight' => 30,
            ]);
            $this->info('⚙️ Default Academic Setting created.');
        }
    }

    protected function ensureStorageDirectories()
    {
        $directories = [
            storage_path('app/public'),
            storage_path('app/purify'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory, 0775, true);
                $this->info("📁 Created directory: $directory");
            }
        }
    }
}
