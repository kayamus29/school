<?php

namespace App\Http\Controllers;

use App\Models\CsvImport;
use App\Services\BulkImport\CsvImportService;
use App\Services\BulkImport\CsvExportService;
use App\Services\BulkImport\Adapters\UserImportAdapter;
use App\Services\BulkImport\Adapters\CourseImportAdapter;
use App\Services\BulkImport\Adapters\AssignedTeacherImportAdapter;
use App\Services\BulkImport\Adapters\StudentImportAdapter;
use App\Services\BulkImport\Adapters\SchoolClassImportAdapter;
use App\Services\BulkImport\Adapters\SectionImportAdapter;
use App\Services\BulkImport\Adapters\PromotionImportAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    /**
     * @var CsvImportService
     */
    protected $importService;

    /**
     * @var CsvExportService
     */
    protected $exportService;

    /**
     * @var array
     */
    protected $adapters = [
        'User' => UserImportAdapter::class,
        'Student' => StudentImportAdapter::class,
        'Course' => CourseImportAdapter::class,
        'Section' => SectionImportAdapter::class,
        'Class' => SchoolClassImportAdapter::class,
        'AssignedTeacher' => AssignedTeacherImportAdapter::class,
        'Promotion' => PromotionImportAdapter::class,
    ];

    /**
     * BulkImportController constructor.
     */
    public function __construct(CsvImportService $importService, CsvExportService $exportService)
    {
        $this->importService = $importService;
        $this->exportService = $exportService;
    }

    /**
     * Display the bulk import dashboard.
     */
    public function index()
    {
        $this->authorize('import_csv');

        $history = CsvImport::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $availableAdapters = array_keys($this->adapters);

        return view('admin.bulk_import.index', compact('history', 'availableAdapters'));
    }

    /**
     * Process the CSV import.
     */
    public function import(Request $request)
    {
        $this->authorize('import_csv');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'adapter_type' => 'required|string|in:' . implode(',', array_keys($this->adapters)),
            'mode' => 'required|string|in:dry_run,real_import',
        ]);

        $adapterClass = $this->adapters[$request->adapter_type];
        $isDryRun = $request->mode === 'dry_run';

        try {
            $result = $this->importService->import(
                $request->file('csv_file'),
                $adapterClass,
                $isDryRun
            );

            // Save history record
            $importRecord = CsvImport::create([
                'user_id' => Auth::id(),
                'adapter_type' => $request->adapter_type,
                'status' => $result->status,
                'file_name' => $request->file('csv_file')->getClientOriginalName(),
                'total_rows' => $result->totalRows,
                'successful_rows' => $result->successful,
                'failed_rows' => $result->failed,
                'errors' => $result->errors,
                'is_dry_run' => $isDryRun,
            ]);

            $message = $isDryRun
                ? "Dry-run completed. Check the preview below."
                : "Import completed with status: " . strtoupper($result->status);

            return back()->with([
                'success' => $message,
                'import_result' => $result->toArray(),
                'last_import_id' => $importRecord->id
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Import Controller Error', [
                'error' => $e->getMessage(),
                'adapter' => $request->adapter_type,
            ]);

            return back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download CSV template for a specific adapter.
     */
    public function downloadTemplate(string $adapter)
    {
        $this->authorize('export_csv');

        if (!isset($this->adapters[$adapter])) {
            return abort(404, 'Adapter not found');
        }

        try {
            return $this->exportService->generateTemplate($this->adapters[$adapter]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Template generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export existing data for a specific adapter.
     */
    public function exportData(string $adapter)
    {
        $this->authorize('export_csv');

        if (!isset($this->adapters[$adapter])) {
            return abort(404, 'Adapter not found');
        }

        try {
            // Define query builders based on adapter type
            $queryBuilder = $this->getQueryBuilderForAdapter($adapter);

            return $this->exportService->export($this->adapters[$adapter], $queryBuilder);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper to get query builder for exports.
     */
    protected function getQueryBuilderForAdapter(string $adapter): callable
    {
        return function ($filters) use ($adapter) {
            switch ($adapter) {
                case 'User':
                case 'Student':
                    return \App\Models\User::query();
                case 'Course':
                    return \App\Models\Course::query();
                case 'Section':
                    return \App\Models\Section::query();
                case 'Class':
                    return \App\Models\SchoolClass::query();
                case 'AssignedTeacher':
                    return \App\Models\AssignedTeacher::query();
                case 'Promotion':
                    return \App\Models\Promotion::query();
                default:
                    throw new \InvalidArgumentException("No query builder defined for adapter: {$adapter}");
            }
        };
    }
}
