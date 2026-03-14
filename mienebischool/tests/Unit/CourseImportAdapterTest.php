<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BulkImport\Adapters\CourseImportAdapter;
use App\Services\BulkImport\CsvValidator;
use App\DTO\CsvImportContext;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\SchoolSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseImportAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected CourseImportAdapter $adapter;
    protected CsvImportContext $context;
    protected SchoolSession $session;
    protected Semester $semester;
    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new CourseImportAdapter(new CsvValidator());
        $this->context = new CsvImportContext(
            schoolId: 'test_school',
            userId: 1,
            adapterClass: CourseImportAdapter::class,
            fileName: 'courses.csv'
        );

        // Create test dependencies
        $this->session = SchoolSession::create(['session_name' => '2023-2024']);
        $this->semester = Semester::create([
            'semester_name' => 'First Term',
            'session_id' => $this->session->id,
            'start_date' => '2023-09-01',
            'end_date' => '2023-12-15',
        ]);
        $this->class = SchoolClass::create(['class_name' => 'Grade 10']);
    }

    /** @test */
    public function it_defines_required_columns()
    {
        $required = $this->adapter->getRequiredColumns();

        $this->assertContains('course_name', $required);
        $this->assertContains('course_type', $required);
        $this->assertContains('class_id', $required);
        $this->assertContains('semester_id', $required);
        $this->assertContains('session_id', $required);
    }

    /** @test */
    public function it_validates_valid_row()
    {
        $row = [
            'Mathematics',
            'Core',
            $this->class->id,
            $this->semester->id,
            $this->session->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /** @test */
    public function it_detects_missing_required_fields()
    {
        $row = [
            'Mathematics',
            '', // Missing course_type
            $this->class->id,
            $this->semester->id,
            $this->session->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertNotEmpty($result->errors);
    }

    /** @test */
    public function it_validates_course_type_enum()
    {
        $row = [
            'Mathematics',
            'InvalidType', // Invalid course type
            $this->class->id,
            $this->semester->id,
            $this->session->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertCount(1, array_filter($result->errors, fn($e) => $e->field === 'course_type'));
    }

    /** @test */
    public function it_validates_foreign_key_existence()
    {
        $row = [
            'Mathematics',
            'Core',
            99999, // Non-existent class_id
            $this->semester->id,
            $this->session->id,
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('not found', $result->errors[0]->message);
    }

    /** @test */
    public function it_detects_duplicate_course_in_csv()
    {
        $row1 = ['Mathematics', 'Core', $this->class->id, $this->semester->id, $this->session->id];
        $row2 = ['Mathematics', 'Core', $this->class->id, $this->semester->id, $this->session->id];

        // First row should pass
        $result1 = $this->adapter->validateRow($row1, 1, $this->context);
        $this->assertTrue($result1->isValid);

        // Second row should fail (duplicate in CSV)
        $result2 = $this->adapter->validateRow($row2, 2, $this->context);
        $this->assertFalse($result2->isValid);
        $this->assertStringContainsString('first seen at line 1', $result2->errors[0]->message);
    }

    /** @test */
    public function it_detects_duplicate_course_in_database()
    {
        Course::create([
            'course_name' => 'Mathematics',
            'course_type' => 'Core',
            'class_id' => $this->class->id,
            'semester_id' => $this->semester->id,
            'session_id' => $this->session->id,
        ]);

        $row = ['Mathematics', 'Core', $this->class->id, $this->semester->id, $this->session->id];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('already exists', $result->errors[0]->message);
    }

    /** @test */
    public function it_builds_model_without_saving()
    {
        $row = ['Mathematics', 'Core', $this->class->id, $this->semester->id, $this->session->id];

        $course = $this->adapter->buildModel($row);

        // Model should be created
        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Mathematics', $course->course_name);
        $this->assertEquals('Core', $course->course_type);

        // But NOT saved to database
        $this->assertNull($course->id);
        $this->assertEquals(0, Course::count());
    }

    /** @test */
    public function it_persists_model_to_database()
    {
        $row = ['Mathematics', 'Core', $this->class->id, $this->semester->id, $this->session->id];

        $course = $this->adapter->buildModel($row);
        $this->assertEquals(0, Course::count());

        $persistedCourse = $this->adapter->persist($course);

        // Now should be saved
        $this->assertNotNull($persistedCourse->id);
        $this->assertEquals(1, Course::count());
    }

    /** @test */
    public function it_generates_example_row()
    {
        $example = $this->adapter->getExampleRow();

        $this->assertArrayHasKey('course_name', $example);
        $this->assertArrayHasKey('course_type', $example);
        $this->assertArrayHasKey('class_id', $example);
        $this->assertNotEmpty($example['course_name']);
    }

    /** @test */
    public function it_allows_same_course_name_for_different_classes()
    {
        $class2 = SchoolClass::create(['class_name' => 'Grade 11']);

        $row1 = ['Mathematics', 'Core', $this->class->id, $this->semester->id, $this->session->id];
        $row2 = ['Mathematics', 'Core', $class2->id, $this->semester->id, $this->session->id];

        $result1 = $this->adapter->validateRow($row1, 1, $this->context);
        $result2 = $this->adapter->validateRow($row2, 2, $this->context);

        // Both should pass (different classes)
        $this->assertTrue($result1->isValid);
        $this->assertTrue($result2->isValid);
    }
}
