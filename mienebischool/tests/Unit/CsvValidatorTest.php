<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BulkImport\CsvValidator;
use App\DTO\CsvImportContext;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected CsvValidator $validator;
    protected CsvImportContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new CsvValidator();
        $this->context = new CsvImportContext(
            schoolId: 'test_school',
            userId: 1,
            adapterClass: 'TestAdapter',
            fileName: 'test.csv'
        );
    }

    /** @test */
    public function it_validates_email_format()
    {
        // Valid email
        $this->assertNull($this->validator->validateEmail('test@example.com', 1));

        // Invalid email
        $error = $this->validator->validateEmail('invalid-email', 1);
        $this->assertNotNull($error);
        $this->assertEquals('email', $error->field);
        $this->assertEquals(1, $error->line);
    }

    /** @test */
    public function it_validates_foreign_key_existence()
    {
        $class = SchoolClass::create(['class_name' => 'Test Class']);

        // Existing FK
        $this->assertNull($this->validator->validateForeignKey(SchoolClass::class, $class->id, 1, 'class_id'));

        // Non-existing FK
        $error = $this->validator->validateForeignKey(SchoolClass::class, 99999, 1, 'class_id');
        $this->assertNotNull($error);
        $this->assertStringContainsString('not found', $error->message);
    }

    /** @test */
    public function it_validates_database_uniqueness()
    {
        User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'role' => 'Student',
            'gender' => 'male',
        ]);

        // Unique value
        $this->assertNull($this->validator->validateUnique('users', 'email', 'new@example.com', 1));

        // Duplicate value
        $error = $this->validator->validateUnique('users', 'email', 'existing@example.com', 1);
        $this->assertNotNull($error);
        $this->assertStringContainsString('already exists', $error->message);
    }

    /** @test */
    public function it_validates_csv_level_uniqueness()
    {
        // First occurrence - should pass
        $error1 = $this->validator->validateUniqueInCsv('email', 'test@example.com', 5, $this->context);
        $this->assertNull($error1);

        // Second occurrence - should fail
        $error2 = $this->validator->validateUniqueInCsv('email', 'test@example.com', 10, $this->context);
        $this->assertNotNull($error2);
        $this->assertEquals('email', $error2->field);
        $this->assertEquals(10, $error2->line);
        $this->assertStringContainsString('first seen at line 5', $error2->message);
    }

    /** @test */
    public function it_validates_date_format()
    {
        // Valid dates
        $this->assertNull($this->validator->validateDate('2023-01-15', 1, 'birthday'));
        $this->assertNull($this->validator->validateDate('15/01/2023', 1, 'birthday'));

        // Invalid date
        $error = $this->validator->validateDate('invalid-date', 1, 'birthday');
        $this->assertNotNull($error);
        $this->assertStringContainsString('Invalid date format', $error->message);
    }

    /** @test */
    public function it_validates_enum_values()
    {
        $allowed = ['Student', 'Teacher', 'Parent'];

        // Valid value
        $this->assertNull($this->validator->validateEnum('Student', $allowed, 1, 'role'));

        // Invalid value
        $error = $this->validator->validateEnum('Invalid', $allowed, 1, 'role');
        $this->assertNotNull($error);
        $this->assertStringContainsString('Must be one of', $error->message);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Valid values
        $this->assertNull($this->validator->validateRequired('value', 1, 'field'));
        $this->assertNull($this->validator->validateRequired('0', 1, 'field'));
        $this->assertNull($this->validator->validateRequired(0, 1, 'field'));

        // Empty values
        $error = $this->validator->validateRequired('', 1, 'field');
        $this->assertNotNull($error);
        $this->assertStringContainsString('is required', $error->message);

        $error = $this->validator->validateRequired(null, 1, 'field');
        $this->assertNotNull($error);
    }

    /** @test */
    public function it_validates_phone_numbers()
    {
        // Valid phone
        $this->assertNull($this->validator->validatePhone('08012345678', 1));

        // Invalid phone (warning, not error)
        $warning = $this->validator->validatePhone('123', 1);
        $this->assertNotNull($warning);
        $this->assertEquals('warning', $warning->severity);
    }

    /** @test */
    public function csv_context_tracks_seen_values_correctly()
    {
        $this->assertFalse($this->context->hasSeen('email', 'test@example.com'));

        $this->context->markAsSeen('email', 'test@example.com', 5);

        $this->assertTrue($this->context->hasSeen('email', 'test@example.com'));
        $this->assertEquals(5, $this->context->getFirstSeenLine('email', 'test@example.com'));
    }
}
