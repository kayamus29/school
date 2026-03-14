<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\CsvImport;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\SchoolSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class BulkImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'Admin']);
        $this->admin->givePermissionTo('import_csv');
        $this->admin->givePermissionTo('export_csv');

        Auth::login($this->admin);

        Config::set('features.csv_import.enabled', true);
        Config::set('features.csv_import.atomic', true);
        Config::set('features.csv_import.security.verify_database', false);
    }

    /** @test */
    public function full_import_workflow_user_adapter()
    {
        // 1. Dry Run with 1 error and 1 good row
        $csvContent = "first_name,last_name,email,role,gender\n";
        $csvContent .= "John,Doe,john@example.com,Student,male\n";
        $csvContent .= "Jane,Smith,invalid-email,Teacher,female\n"; // Error

        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $response = $this->post(route('admin.bulk-import.process'), [
            'csv_file' => $file,
            'adapter_type' => 'User',
            'mode' => 'dry_run',
        ]);

        $response->assertSessionHas('import_result');
        $result = session('import_result');

        $this->assertEquals(2, $result['total_rows']);
        $this->assertEquals(1, $result['failed']);
        $this->assertEquals('preview', $result['status']);
        $this->assertEquals(1, User::where('email', 'john@example.com')->count()); // 1 admin exists, but john shouldn't be saved in dry run

        // 2. Real Import with valid data
        $validCsv = "first_name,last_name,email,role,gender\n";
        $validCsv .= "John,Doe,john@example.com,Student,male\n";
        $validCsv .= "Jane,Smith,jane@example.com,Teacher,female\n";

        $file2 = UploadedFile::fake()->createWithContent('users_valid.csv', $validCsv);

        $response2 = $this->post(route('admin.bulk-import.process'), [
            'csv_file' => $file2,
            'adapter_type' => 'User',
            'mode' => 'real_import',
        ]);

        $response2->assertSessionHas('success');
        $this->assertEquals(3, User::count()); // Admin + 2 new users

        $importRecord = CsvImport::where('is_dry_run', false)->first();
        $this->assertEquals('success', $importRecord->status);
        $this->assertEquals(2, $importRecord->successful_rows);
    }

    /** @test */
    public function course_import_preserves_school_scoping()
    {
        $session = SchoolSession::create(['session_name' => '2024']);
        $semester = Semester::create(['semester_name' => 'Spring', 'session_id' => $session->id, 'start_date' => '2024-01-01', 'end_date' => '2024-06-30']);
        $class = SchoolClass::create(['class_name' => 'Grade 1']);

        $csvContent = "course_name,course_type,class_id,semester_id,session_id\n";
        $csvContent .= "Math,Core,{$class->id},{$semester->id},{$session->id}\n";

        $file = UploadedFile::fake()->createWithContent('courses.csv', $csvContent);

        $this->post(route('admin.bulk-import.process'), [
            'csv_file' => $file,
            'adapter_type' => 'Course',
            'mode' => 'real_import',
        ]);

        $this->assertEquals(1, Course::count());
        $course = Course::first();
        $this->assertEquals($class->id, $course->class_id);
    }
}
