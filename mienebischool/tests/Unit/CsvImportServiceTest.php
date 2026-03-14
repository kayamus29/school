<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BulkImport\CsvImportService;
use App\Services\BulkImport\Adapters\UserImportAdapter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class CsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CsvImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CsvImportService();

        // Create and authenticate a test user
        $admin = User::factory()->create(['role' => 'Admin']);
        Auth::login($admin);

        // Enable features
        Config::set('features.csv_import.enabled', true);
        Config::set('features.csv_import.atomic', true);
        Config::set('features.csv_import.security.verify_database', false); // Disable for testing
    }

    /** @test */
    public function it_imports_valid_csv_in_atomic_mode()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name', 'email', 'role', 'gender'],
            ['John', 'Doe', 'john@example.com', 'Student', 'male'],
            ['Jane', 'Smith', 'jane@example.com', 'Teacher', 'female'],
        ]);

        Config::set('features.csv_import.atomic', true);

        $result = $this->service->import($csv, UserImportAdapter::class, false);

        $this->assertEquals('success', $result->status);
        $this->assertEquals(2, $result->totalRows);
        $this->assertEquals(2, $result->successful);
        $this->assertEquals(0, $result->failed);
        $this->assertEquals(2, User::count());
    }

    /** @test */
    public function it_rolls_back_all_rows_on_error_in_atomic_mode()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name', 'email', 'role', 'gender'],
            ['John', 'Doe', 'john@example.com', 'Student', 'male'],
            ['Jane', 'Smith', 'invalid-email', 'Teacher', 'female'], // Invalid
        ]);

        Config::set('features.csv_import.atomic', true);

        $result = $this->service->import($csv, UserImportAdapter::class, false);

        $this->assertEquals('failed', $result->status);
        $this->assertEquals(2, $result->totalRows);
        $this->assertEquals(0, $result->successful); // Rolled back
        $this->assertNotEmpty($result->errors);
        $this->assertEquals(0, User::count()); // Nothing saved
    }

    /** @test */
    public function it_imports_valid_rows_in_partial_mode()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name', 'email', 'role', 'gender'],
            ['John', 'Doe', 'john@example.com', 'Student', 'male'],
            ['Jane', 'Smith', 'invalid-email', 'Teacher', 'female'], // Invalid
            ['Bob', 'Johnson', 'bob@example.com', 'Student', 'male'],
        ]);

        Config::set('features.csv_import.atomic', false);

        $result = $this->service->import($csv, UserImportAdapter::class, false);

        $this->assertEquals('partial', $result->status);
        $this->assertEquals(3, $result->totalRows);
        $this->assertEquals(2, $result->successful);
        $this->assertEquals(1, $result->failed);
        $this->assertEquals(2, User::count()); // Valid rows saved
    }

    /** @test */
    public function dry_run_does_not_persist_data()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name', 'email', 'role', 'gender'],
            ['John', 'Doe', 'john@example.com', 'Student', 'male'],
            ['Jane', 'Smith', 'jane@example.com', 'Teacher', 'female'],
        ]);

        $result = $this->service->import($csv, UserImportAdapter::class, true); // Dry-run

        $this->assertEquals('preview', $result->status);
        $this->assertEquals(2, $result->totalRows);
        $this->assertEquals(2, $result->successful);
        $this->assertEquals(0, User::count()); // Nothing saved
    }

    /** @test */
    public function dry_run_detects_validation_errors()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name', 'email', 'role', 'gender'],
            ['John', 'Doe', 'invalid-email', 'Student', 'male'],
        ]);

        $result = $this->service->import($csv, UserImportAdapter::class, true);

        $this->assertEquals('preview', $result->status);
        $this->assertNotEmpty($result->errors);
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function it_detects_duplicate_emails_within_csv()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name', 'email', 'role', 'gender'],
            ['John', 'Doe', 'duplicate@example.com', 'Student', 'male'],
            ['Jane', 'Smith', 'duplicate@example.com', 'Teacher', 'female'],
        ]);

        $result = $this->service->import($csv, UserImportAdapter::class, true);

        $this->assertNotEmpty($result->errors);
        $this->assertStringContainsString('first seen at line 2', $result->errors[0]->message);
    }

    /** @test */
    public function it_rejects_file_with_missing_headers()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'last_name'], // Missing required columns
            ['John', 'Doe'],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing required columns');

        $this->service->import($csv, UserImportAdapter::class, false);
    }

    /** @test */
    public function it_rejects_file_with_duplicate_headers()
    {
        $csv = $this->createCsvFile([
            ['first_name', 'first_name', 'email', 'role', 'gender'], // Duplicate
            ['John', 'Doe', 'john@example.com', 'Student', 'male'],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate headers');

        $this->service->import($csv, UserImportAdapter::class, false);
    }

    /**
     * Helper to create CSV file for testing.
     *
     * @param array $rows
     * @return UploadedFile
     */
    protected function createCsvFile(array $rows): UploadedFile
    {
        $tempFile = tmpfile();
        $path = stream_get_meta_data($tempFile)['uri'];

        foreach ($rows as $row) {
            fputcsv($tempFile, $row);
        }

        rewind($tempFile);

        return new UploadedFile(
            $path,
            'test.csv',
            'text/csv',
            null,
            true
        );
    }
}
