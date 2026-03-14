<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentFee;
use App\Models\ClassFee;
use App\Models\BillingBatch;
use App\Models\Promotion;
use App\Interfaces\WalletServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    protected $walletService;

    public function __construct(WalletServiceInterface $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Conduct bulk billing for a specific session and term.
     * Rule: One transaction per student. COMMIT is final for that student.
     */
    public function billTerm($sessionId, $semesterId, $processedByUserId)
    {
        // 1. Validation & Pre-flight
        $classFees = ClassFee::where('session_id', $sessionId)
            ->where('semester_id', $semesterId)
            ->get();

        if ($classFees->isEmpty()) {
            throw new \Exception("No class fee definitions found for this term.");
        }

        // 2. Fetch Eligible Students
        // Rule: status = active AND promotion.session_id = current_session_id
        $students = User::role('student')
            ->where('status', 'active')
            ->whereHas('promotions', function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->with([
                    'promotions' => function ($q) use ($sessionId) {
                        $q->where('session_id', $sessionId);
                    }
                ])
            ->get();

        if ($students->isEmpty()) {
            throw new \Exception("No eligible active students found for this session.");
        }

        // 3. Batch Initiation (Audit Parent)
        $batch = BillingBatch::create([
            'session_id' => $sessionId,
            'semester_id' => $semesterId,
            'processed_by' => $processedByUserId,
            'status' => 'in_progress',
            'batch_meta' => [
                'applied_class_fees' => $classFees->pluck('id')->toArray(),
                'eligible_student_count' => $students->count(),
            ],
        ]);

        $processedCount = 0;
        $totalAmount = 0;

        // 4. Orchestration Loop (Independent Transactions)
        foreach ($students as $student) {
            try {
                // Get student's class for this session
                $promotion = $student->promotions->first();
                if (!$promotion)
                    continue;

                $classId = $promotion->class_id;
                $studentFeesToApply = $classFees->where('class_id', $classId);

                if ($studentFeesToApply->isEmpty())
                    continue;

                // Independent Transaction for this student
                DB::transaction(function () use ($student, $studentFeesToApply, $batch, $sessionId, $semesterId, &$processedCount, &$totalAmount) {
                    foreach ($studentFeesToApply as $feeTemplate) {
                        // 3-Layer Defense Layer 2: Service Idempotency Check
                        $exists = StudentFee::where('student_id', $student->id)
                            ->where('fee_head_id', $feeTemplate->fee_head_id)
                            ->where('session_id', $sessionId)
                            ->where('semester_id', $semesterId)
                            ->exists();

                        if ($exists)
                            continue;

                        // Create the Fee Record
                        $studentFee = StudentFee::create([
                            'student_id' => $student->id,
                            'fee_head_id' => $feeTemplate->fee_head_id,
                            'class_id' => $feeTemplate->class_id,
                            'session_id' => $sessionId,
                            'semester_id' => $semesterId,
                            'amount' => $feeTemplate->amount,
                            'description' => $feeTemplate->description ?? "Termly school fee bulk billing",
                            'status' => 'paid', // Marked as charged to wallet
                            'amount_paid' => $feeTemplate->amount,
                            'balance' => 0,
                            'billing_batch_id' => $batch->id,
                        ]);

                        // Ledger Posting (Reusing WalletService)
                        // This captures the Phase 10 snapshot automatically
                        $this->walletService->charge(
                            $student->id,
                            $feeTemplate->amount,
                            'student_fee',
                            $studentFee->id,
                            "School Fee Billing: " . ($feeTemplate->feeHead->name ?? 'Misc')
                        );

                        $totalAmount += $feeTemplate->amount;
                    }
                    $processedCount++;

                    // Update Audit Parent incrementally
                    $batch->update([
                        'student_count' => $processedCount,
                        'total_amount' => $totalAmount,
                    ]);
                });

            } catch (\Exception $e) {
                Log::error("Bulk Billing Failed for Student ID {$student->id}: " . $e->getMessage());
                // We do NOT stop. Other students must process.
                continue;
            }
        }

        // 5. Finalization
        $batch->update(['status' => 'finalized']);

        return $batch;
    }
}
