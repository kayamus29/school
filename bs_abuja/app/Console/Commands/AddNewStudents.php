<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Section;
use App\Models\SchoolClass; // Needed to find class by name
use App\Models\Promotion; // <--- ADDED THIS LINE
use App\Repositories\UserRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\SchoolSessionRepository;
use Illuminate\Support\Str; // For string manipulation

class AddNewStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:add-new
                            {full_names* : Full names of the students to create (e.g., "Idris Sheriff" "Elazgwu Matthias")}
                            {--class=SS3 : The class name to assign (e.g., "SS3")}
                            {--section=B : The section name to assign (e.g., "B")}
                            {--gender=Male : Default gender for new students (Male|Female)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new students and assigns them to a specified class and section.';

    /**
     * @var UserRepository
     */
    protected $userRepository;

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
    public function __construct(
        UserRepository $userRepository,
        PromotionRepository $promotionRepository,
        SchoolSessionRepository $schoolSessionRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
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
        $fullNames = $this->argument('full_names');
        $className = $this->option('class');
        $sectionName = $this->option('section');
        $gender = $this->option('gender');

        if (empty($fullNames)) {
            $this->error('Please provide at least one student full name.');
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

        // 2. Find Class and Section IDs
        $schoolClass = SchoolClass::where(function($query) use ($className) {
                $query->where('class_name', $className)
                      ->orWhere('class_name', str_replace('SS', 'SS ', $className)); // Handle "SS3" vs "SS 3"
            })
            ->first();

        if (!$schoolClass) {
            $this->error("Class '{$className}' not found. Please check the class name.");
            return Command::FAILURE;
        }
        $classId = $schoolClass->id;
        $this->info("Found class '{$schoolClass->class_name}' with ID: {$classId}");

        $section = Section::where('section_name', $sectionName)
                          ->where('class_id', $classId) // Ensure section belongs to the class
                          ->first();

        if (!$section) {
            $this->error("Section '{$sectionName}' not found for class '{$schoolClass->class_name}'. Please check section name.");
            return Command::FAILURE;
        }
        $sectionId = $section->id;
        $this->info("Found section '{$section->section_name}' with ID: {$sectionId} for class '{$schoolClass->class_name}'");

        $this->line(''); // Blank line

        $createdStudentsCount = 0;
        foreach ($fullNames as $fullName) {
            $this->info("Attempting to create student: {$fullName}");

            $nameParts = explode(' ', $fullName);
            $firstName = array_shift($nameParts);
            $lastName = implode(' ', $nameParts);

            if (empty($lastName)) {
                $this->warn("  Warning: Student '{$fullName}' has no last name. Using first name as last name.");
                $lastName = $firstName;
            }

            // Generate a unique email
            $baseEmail = strtolower(substr($firstName, 0, 1) . '.' . Str::slug($lastName, '') . '@abuja.bestsolution.ng');
            $email = $baseEmail;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = strtolower(substr($firstName, 0, 1) . '.' . Str::slug($lastName, '') . $counter . '@abuja.bestsolution.ng');
                $counter++;
            }

            // Generate a unique ID card number (simple increment for demonstration, improve in production)
            // In a real system, you might have a service for this or retrieve the latest and increment
            $lastSn = (int) Str::afterLast(Promotion::max('id_card_number') ?? 'BS/ABJ/2026/0', '/');
            $idCardNumber = 'BS/ABJ/2026/' . ($lastSn + $createdStudentsCount + 1);


            $requestData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'gender' => $gender, // Use option or default
                'nationality' => 'Nigerian',
                'phone' => 'N/A',
                'address' => 'N/A',
                'address2' => '-', // Corrected from null to '-'
                'city' => 'Abuja',
                'zip' => '900001',
                'photo' => null,
                'birthday' => now()->subYears(15)->format('Y-m-d'), // Default to 15 years ago
                'religion' => 'N/A',
                'blood_type' => null,
                'password' => 'password', // Default password
                'father_name' => 'N/A',
                'father_phone' => 'N/A',
                'mother_name' => 'N/A',
                'mother_phone' => 'N/A',
                'parent_address' => 'N/A',
                'class_id' => $classId,
                'section_id' => $sectionId,
                'session_id' => $currentSessionId,
                'board_reg_no' => null,
                'id_card_number' => $idCardNumber,
            ];

            try {
                // The bulk_create_students.php script calls userRepository->createStudent
                // which internally handles User, StudentParentInfo, StudentAcademicInfo, Promotion, Wallet
                $this->userRepository->createStudent($requestData);
                $createdStudentsCount++;
                $this->info("  [SUCCESS] Created '{$fullName}'. Email: {$email}, ID Card: {$idCardNumber}");
            } catch (\Exception $e) {
                $this->error("  [ERROR] Failed to create student '{$fullName}': " . $e->getMessage());
            }
            $this->line(''); // Blank line
        }

        if ($createdStudentsCount > 0) {
            $this->info("Finished. Successfully created {$createdStudentsCount} student(s) in '{$className} {$sectionName}'.");
            return Command::SUCCESS;
        } else {
            $this->warn('No students were created.');
            return Command::FAILURE;
        }
    }
}
