<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateFinancialLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:migrate-legacy-data {--force : Clear existing student_fees first}';
    protected $description = 'Reconcile legacy class fees and payments into the new ledger structure.';

    public function handle()
    {
        $this->info('Starting legacy financial data migration...');

        if ($this->option('force')) {
            $this->warn('Clearing existing student_fees ledger...');
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \App\Models\StudentFee::query()->update(['transferred_to_id' => null]);
            \App\Models\StudentPayment::query()->update(['student_fee_id' => null]);
            \App\Models\StudentFee::truncate();
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $students = \App\Models\User::where('role', 'student')->get();
        $totalFeesCreated = 0;
        $totalPaymentsLinked = 0;

        foreach ($students as $student) {
            $this->comment("Processing student: {$student->first_name} {$student->last_name} (ID: {$student->id})");

            // 1. Reconstruct Fees from Class Assignments
            $promotions = $student->promotions()->with(['session', 'schoolClass'])->get();
            foreach ($promotions as $promotion) {
                $classFees = \App\Models\ClassFee::where('class_id', $promotion->class_id)
                    ->where('session_id', $promotion->session_id)
                    ->get();

                foreach ($classFees as $classFee) {
                    if (!$classFee->feeHead) {
                        $this->warn("Skipping ClassFee ID: {$classFee->id} - Missing FeeHead");
                        continue;
                    }

                    // Avoid duplicate creation
                    $fee = \App\Models\StudentFee::firstOrCreate([
                        'student_id' => $student->id,
                        'fee_head_id' => $classFee->fee_head_id,
                        'session_id' => $promotion->session_id,
                        'semester_id' => $classFee->semester_id,
                    ], [
                        'fee_type' => 'tuition',
                        'reference' => "School Fees - " . ($classFee->feeHead->name ?? 'Unknown'),
                        'amount' => $classFee->amount,
                        'amount_paid' => 0,
                        'balance' => $classFee->amount,
                        'status' => 'unpaid',
                        'description' => $classFee->description ?? 'Legacy fee assignment'
                    ]);

                    if ($fee->wasRecentlyCreated) {
                        $totalFeesCreated++;
                    }
                }
            }

            // 2. Link Legacy Payments
            $legacyPayments = \App\Models\StudentPayment::where('student_id', $student->id)
                ->whereNull('student_fee_id')
                ->orderBy('transaction_date')
                ->get();

            foreach ($legacyPayments as $payment) {
                // Try to find an unpaid fee in the same session/semester/class
                // Note: legacy payments use school_session_id
                $fee = \App\Models\StudentFee::where('student_id', $student->id)
                    ->where('session_id', $payment->school_session_id)
                    ->where('semester_id', $payment->semester_id)
                    ->where('balance', '>', 0)
                    ->first();

                // If not found in same term, find ANY outstanding fee (legacy behavior was less strict)
                if (!$fee) {
                    $fee = \App\Models\StudentFee::where('student_id', $student->id)
                        ->where('balance', '>', 0)
                        ->orderBy('session_id')
                        ->orderBy('semester_id')
                        ->first();
                }

                if ($fee) {
                    $payment->student_fee_id = $fee->id;
                    $payment->save();

                    $fee->amount_paid += $payment->amount_paid;
                    $fee->balance = max(0, $fee->amount - $fee->amount_paid);
                    $fee->status = ($fee->balance == 0) ? 'paid' : 'partial';
                    $fee->save();

                    $totalPaymentsLinked++;
                } else {
                    $this->error("Could not find matching fee for payment ID: {$payment->id} (Amount: {$payment->amount_paid})");
                }
            }
        }

        $this->info("Migration completed!");
        $this->info("Total Student Fees Created: {$totalFeesCreated}");
        $this->info("Total Payments Re-linked: {$totalPaymentsLinked}");

        // Reconciliation Proof
        $historicalTotal = \App\Models\StudentPayment::sum('amount_paid');
        $ledgerTotal = \App\Models\StudentFee::sum('amount_paid');

        if (abs($historicalTotal - $ledgerTotal) < 0.01) {
            $this->info("RECONCILIATION SUCCESS: Ledger matches historical payments (Total: {$historicalTotal})");
        } else {
            $this->error("RECONCILIATION ERROR: Ledger mismatch!");
            $this->error("Historical: {$historicalTotal}, Ledger: {$ledgerTotal}");
        }

        return 0;
    }
}
