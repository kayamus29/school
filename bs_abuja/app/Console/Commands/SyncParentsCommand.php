<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentParentInfo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SyncParentsCommand extends Command
{
    protected $signature = 'schools:sync-parents';
    protected $description = 'Syncs parents from StudentParentInfo to User table based on guardian_email and links them.';

    private $stats = [
        'total_emails' => 0,
        'parents_created' => 0,
        'parents_reused' => 0,
        'students_linked' => 0,
        'errors' => []
    ];

    public function handle()
    {
        $this->info('Starting Parent Sync...');
        $this->info('========================================');

        // 1. Normalize all guardian emails first
        $this->normalizeEmails();

        // 2. Get all StudentParentInfo records where guardian_email is set
        $infos = StudentParentInfo::whereNotNull('guardian_email')
            ->where('guardian_email', '!=', '')
            ->get();

        $grouped = $infos->groupBy('guardian_email');
        $this->stats['total_emails'] = $grouped->count();

        $this->info("Found {$this->stats['total_emails']} unique guardian emails.");
        $this->newLine();

        foreach ($grouped as $email => $records) {
            $childCount = $records->count();
            $this->line("Processing: $email ($childCount children)");

            try {
                DB::beginTransaction();

                // Find or Create User (Idempotent)
                $parentUser = User::where('email', $email)->first();

                if (!$parentUser) {
                    // Get name from first record
                    $parentName = $records->first()->father_name ?: ($records->first()->mother_name ?: 'Parent');
                    $parts = explode(' ', trim($parentName), 2);
                    $firstName = $parts[0];
                    $lastName = $parts[1] ?? 'Guardian';

                    // Create with ONLY the fields that exist in the database
                    $parentUser = new User();
                    $parentUser->first_name = $firstName;
                    $parentUser->last_name = $lastName;
                    $parentUser->email = $email;
                    $parentUser->password = Hash::make('password');
                    $parentUser->role = 'parent';
                    $parentUser->gender = 'Other';
                    $parentUser->nationality = 'N/A';
                    $parentUser->phone = $records->first()->guardian_phone ?? 'N/A';
                    $parentUser->address = $records->first()->parent_address ?? 'N/A';
                    $parentUser->city = 'N/A';
                    $parentUser->zip = 'N/A';
                    $parentUser->save();

                    // Assign Spatie role
                    $parentUser->assignRole('Parent');

                    $this->stats['parents_created']++;
                    $this->info("  ✓ Created new parent user (ID: {$parentUser->id})");
                } else {
                    // Ensure role is set
                    if (!$parentUser->hasRole('Parent')) {
                        $parentUser->assignRole('Parent');
                    }
                    $this->stats['parents_reused']++;
                    $this->info("  ✓ Reused existing parent user (ID: {$parentUser->id})");
                }

                // Link all children records to this parent_user_id (Idempotent)
                foreach ($records as $record) {
                    if ($record->parent_user_id !== $parentUser->id) {
                        $record->parent_user_id = $parentUser->id;
                        $record->save();
                        $this->stats['students_linked']++;
                    }
                }

                DB::commit();
                $this->line("  ✓ Linked $childCount student(s)");

            } catch (\Exception $e) {
                DB::rollBack();
                $errorMsg = $e->getMessage();
                $this->error("  ✗ Failed: $errorMsg");
                $this->stats['errors'][] = [
                    'email' => $email,
                    'error' => $errorMsg
                ];
            }

            $this->newLine();
        }

        $this->displaySummary();

        return count($this->stats['errors']) > 0 ? 1 : 0;
    }

    private function normalizeEmails()
    {
        $this->info('Normalizing guardian emails...');

        $infos = StudentParentInfo::whereNotNull('guardian_email')
            ->where('guardian_email', '!=', '')
            ->get();

        $normalized = 0;
        foreach ($infos as $info) {
            $original = $info->guardian_email;
            $cleaned = strtolower(trim($original));

            if ($original !== $cleaned) {
                $info->guardian_email = $cleaned;
                $info->save();
                $normalized++;
            }
        }

        $this->info("Normalized $normalized email(s)");
        $this->newLine();
    }

    private function displaySummary()
    {
        $this->info('========================================');
        $this->info('SYNC SUMMARY');
        $this->info('========================================');
        $this->line("Total unique emails: {$this->stats['total_emails']}");
        $this->line("Parents created: {$this->stats['parents_created']}");
        $this->line("Parents reused: {$this->stats['parents_reused']}");
        $this->line("Students linked: {$this->stats['students_linked']}");

        if (count($this->stats['errors']) > 0) {
            $this->error("Errors: " . count($this->stats['errors']));
            $this->newLine();
            $this->error('Failed Records:');
            foreach ($this->stats['errors'] as $error) {
                $this->error("  - {$error['email']}: {$error['error']}");
            }
        } else {
            $this->info("Errors: 0");
            $this->newLine();
            $this->info('✓ All parents synced successfully!');
        }

        $this->info('========================================');
    }
}
