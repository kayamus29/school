<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BulkImport\Adapters\AssignedTeacherImportAdapter;
use App\Services\BulkImport\CsvValidator;
use App\DTO\CsvImportContext;
use App\Models\AssignedTeacher;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Course;
use App\Models\Semester;
use App\Models\SchoolSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignedTeacherImportAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected AssignedTeacherImportAdapter $adapter;
    protected CsvImportContext $context;
    protected User $teacher;
    protected SchoolSession $session;
    protected Semester $semester;
    protected SchoolClass $class;
    protected Section $section;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new AssignedTeacherImportAdapter(new CsvValidator());
        $this->context = new CsvImportContext(
            schoolId: 'test_school',
            userId: 1,
            adapterClass: AssignedTeacherImportAdapter::class,
            fileName: 'assigned_teachers.csv'
        );

        // Create test dependencies
        $this->teacher = User::factory()->create(['role' => 'Teacher']);
        $this->session = SchoolSession::create(['session_name' => '2023-2024']);
        $this->semester = Semester::create([
            'semester_name' => 'First Term',
            'session_id' => $this->session->id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-12-15',
        ]);
        $this->class = SchoolClass::create(['class_name' => 'Grade 10']);
        $this->section = Section::create([
            'section_name' => 'A',
            'class_id' => $this->class->id,
            'room_no' => '101',
        ]);
        $this->course = Course::create([
            'course_name' => 'Mathematics',
            'course_type' => 'Core',
            'class_id' => $this->class->id,
            'semester_id' => $this->semester->id,
            'session_id' => $this->session->id,
        ]);
    }

    /** @test */
    public function it_defines_required_and_optional_columns()
    {
        $required = $this->adapter->getRequiredColumns();
        $this->assertContains('teacher_id', $required);
        $this->assertContains('semester_id', $required);
        $this->assertContains('class_id', $required);
        $this->assertContains('session_id', $required);

        $optional = $this->adapter->getOptionalColumns();
        $this->assertContains('section_id', $optional);
        $this->assertContains('course_id', $optional);
    }

    /** @test */
    public function it_validates_valid_row_with_section()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            '',
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /** @test */
    public function it_validates_valid_row_with_course()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            '',
            $this->course->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /** @test */
    public function it_validates_valid_row_with_both_section_and_course()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            $this->course->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /** @test */
    public function it_requires_either_section_or_course()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            '', // No section_id
            '', // No course_id
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Either section_id or course_id', $result->errors[0]->message);
    }

    /** @test */
    public function it_validates_teacher_role()
    {
        $student = User::factory()->create(['role' => 'Student']);

        $row = [
            $student->id, // Not a teacher
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            '',
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('not a teacher', $result->errors[0]->message);
    }

    /** @test */
    public function it_validates_foreign_key_existence()
    {
        $row = [
            $this->teacher->id,
            99999, // Non-existent semester_id
            $this->class->id,
            $this->session->id,
            $this->section->id,
            '',
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('not found', $result->errors[0]->message);
    }

    /** @test */
    public function it_detects_duplicate_assignment_in_csv()
    {
        $row1 = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            $this->course->id,
        ];
        $row2 = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            $this->course->id,
        ];

        // First row should pass
        $result1 = $this->adapter->validateRow($row1, 1, $this->context);
        $this->assertTrue($result1->isValid);

        // Second row should fail (duplicate in CSV)
        $result2 = $this->adapter->validateRow($row2, 2, $this->context);
        $this->assertFalse($result2->isValid);
        $this->assertStringContainsString('first seen at line 1', $result2->errors[0]->message);
    }

    /** @test */
    public function it_detects_duplicate_assignment_in_database()
    {
        AssignedTeacher::create([
            'teacher_id' => $this->teacher->id,
            'semester_id' => $this->semester->id,
            'class_id' => $this->class->id,
            'session_id' => $this->session->id,
            'section_id' => $this->section->id,
            'course_id' => $this->course->id,
        ]);

        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            $this->course->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('already exists', $result->errors[0]->message);
    }

    /** @test */
    public function it_builds_model_without_saving()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            $this->course->id,
        ];

        $assignment = $this->adapter->buildModel($row);

        // Model should be created
        $this->assertInstanceOf(AssignedTeacher::class, $assignment);
        $this->assertEquals($this->teacher->id, $assignment->teacher_id);

        // But NOT saved to database
        $this->assertNull($assignment->id);
        $this->assertEquals(0, AssignedTeacher::count());
    }

    /** @test */
    public function it_persists_model_to_database()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            $this->section->id,
            '',
        ];

        $assignment = $this->adapter->buildModel($row);
        $this->assertEquals(0, AssignedTeacher::count());

        $persistedAssignment = $this->adapter->persist($assignment);

        // Now should be saved
        $this->assertNotNull($persistedAssignment->id);
        $this->assertEquals(1, AssignedTeacher::count());
    }

    /** @test */
    public function it_handles_nullable_section_and_course()
    {
        $row = [
            $this->teacher->id,
            $this->semester->id,
            $this->class->id,
            $this->session->id,
            '', // Empty section_id
            $this->course->id,
        ];

        $assignment = $this->adapter->buildModel($row);

        $this->assertNull($assignment->section_id);
        $this->assertEquals($this->course->id, $assignment->course_id);
    }

    /** @test */
    public function it_generates_example_row()
    {
        $example = $this->adapter->getExampleRow();

        $this->assertArrayHasKey('teacher_id', $example);
        $this->assertArrayHasKey('semester_id', $example);
        $this->assertArrayHasKey('section_id', $example);
        $this->assertArrayHasKey('course_id', $example);
        $this->assertNotEmpty($example['teacher_id']);
    }
}
