<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BulkImport\Adapters\UserImportAdapter;
use App\Services\BulkImport\CsvValidator;
use App\DTO\CsvImportContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserImportAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected UserImportAdapter $adapter;
    protected CsvImportContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new UserImportAdapter(new CsvValidator());
        $this->context = new CsvImportContext(
            schoolId: 'test_school',
            userId: 1,
            adapterClass: UserImportAdapter::class,
            fileName: 'users.csv'
        );
    }

    /** @test */
    public function it_defines_required_and_optional_columns()
    {
        $required = $this->adapter->getRequiredColumns();
        $this->assertContains('first_name', $required);
        $this->assertContains('email', $required);
        $this->assertContains('role', $required);

        $optional = $this->adapter->getOptionalColumns();
        $this->assertContains('phone', $optional);
        $this->assertContains('password', $optional);
    }

    /** @test */
    public function it_validates_valid_row()
    {
        $row = [
            'John',
            'Doe',
            'john@example.com',
            'Student',
            'male',
            'password123',
            '08012345678',
            '123 Main St',
            '2010-05-15',
            'Nigerian',
            'O+',
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /** @test */
    public function it_detects_missing_required_fields()
    {
        $row = [
            'John',
            '', // Missing last_name
            'john@example.com',
            'Student',
            'male',
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertNotEmpty($result->errors);
    }

    /** @test */
    public function it_detects_invalid_email()
    {
        $row = [
            'John',
            'Doe',
            'invalid-email', // Invalid
            'Student',
            'male',
        ];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertCount(1, array_filter($result->errors, fn($e) => $e->field === 'email'));
    }

    /** @test */
    public function it_detects_duplicate_email_in_csv()
    {
        $row1 = ['John', 'Doe', 'duplicate@example.com', 'Student', 'male'];
        $row2 = ['Jane', 'Smith', 'duplicate@example.com', 'Teacher', 'female'];

        // First row should pass
        $result1 = $this->adapter->validateRow($row1, 1, $this->context);
        $this->assertTrue($result1->isValid);

        // Second row should fail (duplicate in CSV)
        $result2 = $this->adapter->validateRow($row2, 2, $this->context);
        $this->assertFalse($result2->isValid);
        $this->assertStringContainsString('first seen at line 1', $result2->errors[0]->message);
    }

    /** @test */
    public function it_detects_duplicate_email_in_database()
    {
        User::create([
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'role' => 'Student',
            'gender' => 'male',
        ]);

        $row = ['John', 'Doe', 'existing@example.com', 'Student', 'male'];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('already exists in database', $result->errors[0]->message);
    }

    /** @test */
    public function it_validates_role_enum()
    {
        $row = ['John', 'Doe', 'john@example.com', 'InvalidRole', 'male'];

        $result = $this->adapter->validateRow($row, 1, $this->context);

        $this->assertFalse($result->isValid);
        $this->assertCount(1, array_filter($result->errors, fn($e) => $e->field === 'role'));
    }

    /** @test */
    public function it_builds_model_without_saving()
    {
        $row = [
            'John',
            'Doe',
            'john@example.com',
            'Student',
            'male',
            'password123',
            '08012345678',
            '123 Main St',
            '2010-05-15',
            'Nigerian',
            'O+',
        ];

        $user = $this->adapter->buildModel($row);

        // Model should be created
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('john@example.com', $user->email);

        // But NOT saved to database
        $this->assertNull($user->id);
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function it_persists_model_to_database()
    {
        $row = ['John', 'Doe', 'john@example.com', 'Student', 'male'];

        $user = $this->adapter->buildModel($row);
        $this->assertEquals(0, User::count());

        $persistedUser = $this->adapter->persist($user);

        // Now should be saved
        $this->assertNotNull($persistedUser->id);
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function it_hashes_password_in_build_model()
    {
        $row = ['John', 'Doe', 'john@example.com', 'Student', 'male', 'mypassword'];

        $user = $this->adapter->buildModel($row);

        $this->assertTrue(Hash::check('mypassword', $user->password));
    }

    /** @test */
    public function it_uses_default_password_when_not_provided()
    {
        $row = ['John', 'Doe', 'john@example.com', 'Student', 'male'];

        $user = $this->adapter->buildModel($row);

        $this->assertTrue(Hash::check('changeme', $user->password));
    }

    /** @test */
    public function it_generates_example_row()
    {
        $example = $this->adapter->getExampleRow();

        $this->assertArrayHasKey('first_name', $example);
        $this->assertArrayHasKey('email', $example);
        $this->assertArrayHasKey('role', $example);
        $this->assertNotEmpty($example['first_name']);
    }

    /** @test */
    public function persist_syncs_spatie_roles()
    {
        $row = ['John', 'Doe', 'john@example.com', 'Student', 'male'];

        $user = $this->adapter->buildModel($row);
        $persistedUser = $this->adapter->persist($user);

        // Verify role was synced (if Spatie is configured)
        if (method_exists($persistedUser, 'hasRole')) {
            $this->assertTrue($persistedUser->hasRole('Student'));
        }
    }
}
