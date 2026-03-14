<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\CsvImport;
use App\Services\BulkImport\CsvImportService;
use App\Services\BulkImport\CsvExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

class BulkImportControllerTest extends TestCase
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

        // Ensure config is set
        Config::set('features.csv_import.enabled', true);
        Config::set('features.csv_import.security.verify_database', false);
    }

    /** @test */
    public function index_requires_import_csv_permission()
    {
        $user = User::factory()->create(['role' => 'Teacher']);
        Auth::login($user);

        $response = $this->get(route('admin.bulk-import.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function index_displays_dashboard_for_authorized_users()
    {
        $response = $this->get(route('admin.bulk-import.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.bulk_import.index');
        $response->assertViewHas('availableAdapters');
    }

    /** @test */
    public function import_validates_required_fields()
    {
        $response = $this->post(route('admin.bulk-import.process'), []);

        $response->assertSessionHasErrors(['csv_file', 'adapter_type', 'mode']);
    }

    /** @test */
    public function import_handles_dry_run_correctly()
    {
        $csv = UploadedFile::fake()->create('test.csv', 100);

        $response = $this->post(route('admin.bulk-import.process'), [
            'csv_file' => $csv,
            'adapter_type' => 'Course',
            'mode' => 'dry_run',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_result');
        $response->assertSessionHas('last_import_id');

        // Verify history was saved
        $import = CsvImport::first();
        $this->assertNotNull($import);
        $this->assertTrue($import->is_dry_run);
        $this->assertEquals('Course', $import->adapter_type);
    }

    /** @test */
    public function template_download_requires_export_permission()
    {
        $user = User::factory()->create(['role' => 'Teacher']);
        Auth::login($user);

        $response = $this->get(route('admin.bulk-import.template', 'User'));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_fails_if_adapter_is_invalid()
    {
        $csv = UploadedFile::fake()->create('test.csv', 100);

        $response = $this->post(route('admin.bulk-import.process'), [
            'csv_file' => $csv,
            'adapter_type' => 'InvalidAdapter',
            'mode' => 'dry_run',
        ]);

        $response->assertSessionHasErrors('adapter_type');
    }
}
