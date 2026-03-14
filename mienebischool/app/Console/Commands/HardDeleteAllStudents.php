<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\StudentParentInfo;
use App\Models\StudentAcademicInfo;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

class HardDeleteAllStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:hard-delete-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently deletes all student records and their associated data.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->confirm('This will PERMANENTLY DELETE ALL student records and their associated parent info, academic info, and promotion records. This action is irreversible. Do you wish to continue?')) {
            $this->info('Student deletion cancelled.');
            return 0;
        }

        $this->info('Initiating hard delete of all student records...');

        DB::beginTransaction();

        try {
            $studentUserIds = User::where('role', 'student')->pluck('id')->toArray();

            if (empty($studentUserIds)) {
                $this->info('No student records found to delete.');
                DB::rollBack(); // Nothing to delete, so rollback the transaction.
                return 0;
            }

            // Delete associated records first
            StudentParentInfo::whereIn('student_id', $studentUserIds)->delete();
            $this->info('Deleted ' . count($studentUserIds) . ' StudentParentInfo records.');

            StudentAcademicInfo::whereIn('student_id', $studentUserIds)->delete();
            $this->info('Deleted ' . count($studentUserIds) . ' StudentAcademicInfo records.');

            Promotion::whereIn('student_id', $studentUserIds)->delete();
            $this->info('Deleted ' . count($studentUserIds) . ' Promotion records.');

            // Finally, delete the User records for students
            User::whereIn('id', $studentUserIds)->where('role', 'student')->delete();
            $this->info('Deleted ' . count($studentUserIds) . ' student User records.');

            DB::commit();
            $this->info('All student records and associated data have been permanently deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred during student deletion: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
