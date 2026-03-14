<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Section;
use App\Models\Promotion;
use App\Repositories\PromotionRepository;
use App\Repositories\SchoolSessionRepository; // Assuming this exists to get latest session

class MoveStudentsSection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:move-section
                            {student_id_card_numbers* : One or more ID card numbers of students to move}
                            {--from=B : The name of the section to move students FROM (e.g., "B")}
                            {--to=A : The name of the section to move students TO (e.g., "A")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves specified students from one section to another within the current academic session.';

    /**
     * @var PromotionRepository
     */
    protected $promotionRepository;

    /**
     * @var SchoolSessionRepository
     */
    protected $schoolSessionRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PromotionRepository $promotionRepository, SchoolSessionRepository $schoolSessionRepository)
    {
        parent::__construct();
        $this->promotionRepository = $promotionRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $targetStudentIdCardNumbers = $this->argument('student_id_card_numbers');
        $fromSectionName = $this->option('from');
        $toSectionName = $this->option('to');

        if (empty($targetStudentIdCardNumbers)) {
            $this->error('No student ID card numbers provided. Please specify at least one.');
            return Command::INVALID;
        }

        // 1. Get current academic session
        $currentSession = $this->schoolSessionRepository->getLatestSession();
        if (!$currentSession) {
            $this->error('No current academic session found. Cannot proceed.');
            return Command::FAILURE;
        }
        $currentSessionId = $currentSession->id;
        $this->info("Operating in academic session: {$currentSession->session_name} (ID: {$currentSessionId})");

        // 2. Get Section IDs for 'from' and 'to' sections
        $fromSection = Section::where('section_name', $fromSectionName)->first();
        if (!$fromSection) {
            $this->error("Section '{$fromSectionName}' not found. Please ensure the section name is correct.");
            return Command::FAILURE;
        }
        $fromSectionId = $fromSection->id;
        $this->info("Found '{$fromSectionName}' with ID: {$fromSectionId}");

        $toSection = Section::where('section_name', $toSectionName)->first();
        if (!$toSection) {
            $this->error("Section '{$toSectionName}' not found. Please ensure the section name is correct.");
            return Command::FAILURE;
        }
        $toSectionId = $toSection->id;
        $this->info("Found '{$toSectionName}' with ID: {$toSectionId}");

        $studentsToUpdate = [];
        $movedCount = 0;

        $this->line(''); // Add a blank line for readability

        foreach ($targetStudentIdCardNumbers as $idCardNumber) {
            $this->info("Processing student with ID Card Number: {$idCardNumber}");
            // Find the promotion record for the student in the current session and 'from' section
            $promotion = Promotion::where('id_card_number', $idCardNumber)
                                  ->where('session_id', $currentSessionId)
                                  ->where('section_id', $fromSectionId) // Ensure they are in the 'from' section
                                  ->first();

            if ($promotion) {
                // Ensure the student exists and is a 'student' role in the User model
                $studentUser = User::find($promotion->student_id);
                if (!$studentUser || $studentUser->role !== 'student') {
                    $this->warn("  Skipping student: User for ID card '{$idCardNumber}' not found or not a student.");
                    continue;
                }

                $this->comment("  -> Preparing to move '{$studentUser->first_name} {$studentUser->last_name}' (ID: {$promotion->student_id})");
                $this->comment("     from section '{$fromSectionName}' to '{$toSectionName}'.");


                $studentsToUpdate[] = [
                    'student_id'       => $promotion->student_id,
                    'session_id'       => $currentSessionId,
                    'class_id'         => $promotion->class_id, // Keep the same class
                    'section_id'       => $toSectionId,          // Assign the new section ID
                    'id_card_number'   => $idCardNumber,
                ];
            } else {
                $this->warn("  -> Could not find promotion record for ID card '{$idCardNumber}' in current session ({$currentSessionId}) and section '{$fromSectionName}'. Skipping.");
            }
        }

        $this->line(''); // Add a blank line for readability

        if (empty($studentsToUpdate)) {
            $this->info('No students found to move. Exiting.');
            return Command::SUCCESS;
        }

        if (!$this->confirm("Found " . count($studentsToUpdate) . " student(s) to move. Do you wish to proceed?")) {
            $this->info('Operation cancelled by user.');
            return Command::SUCCESS;
        }

        // 4. Execute the mass promotion (update)
        try {
            // The massPromotion method handles updateOrCreate internally,
            // so passing the student's existing data with the new section_id will update their record.
            $this->promotionRepository->massPromotion($studentsToUpdate);
            $movedCount = count($studentsToUpdate);
            $this->info("Successfully moved {$movedCount} student(s) from '{$fromSectionName}' to '{$toSectionName}'.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during student section update: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getMessage()); // More detailed error
            return Command::FAILURE;
        }
    }
}
