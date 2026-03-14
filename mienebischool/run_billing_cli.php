<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\SchoolSession;
use App\Services\BillingService;
use App\Services\WalletService; // Assuming basic implementation or bound
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Billing Execution Runner (REAL) ---\n";

$sessionId = 1;
$semesterId = 1;
$userId = 1; // Assuming Admin ID 1

// Mock Auth::id() if needed or pass explicitly
// BillingService uses passed ID, so we are good.

// Resolve Service
try {
    $walletService = app(App\Interfaces\WalletServiceInterface::class);
    $billingService = new BillingService($walletService);

    echo "Service Instantiated.\n";
    echo "Starting Bill Term process for Session $sessionId, Term $semesterId...\n";

    $batch = $billingService->billTerm($sessionId, $semesterId, $userId);

    echo "--- BATCH RESULT ---\n";
    echo "Batch ID: " . $batch->id . "\n";
    echo "Status: " . $batch->status . "\n";
    echo "Student Count: " . $batch->student_count . "\n";
    echo "Total Amount: " . $batch->total_amount . "\n";

} catch (Exception $e) {
    echo "\nCRITICAL ERROR OUTSIDE LOOP: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
