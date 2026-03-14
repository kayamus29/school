<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\StudentParentInfo;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CleanupParentsCommand extends Command
{
    protected $signature = 'schools:cleanup-parents';
    protected $description = 'Remove Parent users and the Parent role from the database';

    public function handle()
    {
        $this->info('Starting Parent cleanup...');

        DB::beginTransaction();

        try {
            // 1. Unlink students
            $unlinkedCount = StudentParentInfo::whereNotNull('parent_user_id')->update(['parent_user_id' => null]);
            $this->info("Unlinked $unlinkedCount student parent info records.");

            // 2. Delete Parent Users
            // Find users by Spatie role first
            $parentRole = Role::where('name', 'Parent')->first();
            $userCount = 0;

            if ($parentRole) {
                $users = $parentRole->users;
                $userCount = $users->count();
                foreach ($users as $user) {
                    $user->delete();
                }
                $this->info("Deleted $userCount users with Spatie Parent role.");
            }

            // Also check role column just in case
            $columnUserCount = User::where('role', 'parent')->count();
            if ($columnUserCount > 0) {
                User::where('role', 'parent')->delete();
                $this->info("Deleted $columnUserCount users with 'parent' in role column.");
            }

            // 3. Delete the Role
            if ($parentRole) {
                $parentRole->delete();
                $this->info("Deleted 'Parent' role.");
            }

            DB::commit();
            $this->info('Cleanup completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Cleanup failed: ' . $e->getMessage());
        }
    }
}
