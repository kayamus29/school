<?php

namespace App\Services;

use App\Models\StudentFee;
use App\Models\StudentPayment;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Record a payment and update the associated student fee balance.
     *
     * @param StudentPayment $payment
     * @return void
     */
    public function recordPayment(StudentPayment $payment)
    {
        if (!$payment->student_fee_id) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $fee = StudentFee::lockForUpdate()->find($payment->student_fee_id);

            if ($fee) {
                $fee->amount_paid += $payment->amount_paid;
                $fee->balance = $fee->amount - $fee->amount_paid;

                if ($fee->balance <= 0) {
                    $fee->status = 'paid';
                    $fee->balance = 0; // Prevent negative balance
                } elseif ($fee->amount_paid > 0) {
                    $fee->status = 'partial';
                } else {
                    $fee->status = 'unpaid';
                }

                $fee->save();
            }
        });
    }

    /**
     * Explicitly carry forward unpaid balances to a new term/session.
     *
     * @param int $student_id
     * @param int $new_session_id
     * @param int|null $new_semester_id If null, uses first semester of new session
     * @return void
     */
    public function carryForwardArrears($student_id, $new_session_id, $new_semester_id = null)
    {
        // If semester not provided, use first semester of the new session
        if ($new_semester_id === null) {
            $firstSemester = \DB::table('semesters')
                ->where('session_id', $new_session_id)
                ->orderBy('id', 'asc')
                ->first();

            if (!$firstSemester) {
                // No semesters defined for this session - skip arrears carry-forward
                \Log::warning("Cannot carry forward arrears: No semesters found for session {$new_session_id}");
                return;
            }

            $new_semester_id = $firstSemester->id;
        }

        // 1. Find all historical unpaid/partial fees for this student
        // Exclude current session/semester and exclude fees already transferred
        $unpaidFees = StudentFee::where('student_id', $student_id)
            ->where('balance', '>', 0)
            ->where(function ($query) use ($new_session_id, $new_semester_id) {
                $query->where('session_id', '!=', $new_session_id)
                    ->orWhere('semester_id', '!=', $new_semester_id);
            })
            ->whereNull('transferred_to_id')
            ->get();

        foreach ($unpaidFees as $fee) {
            DB::transaction(function () use ($fee, $student_id, $new_session_id, $new_semester_id) {
                // Use a unique reference to prevent collisions and allow for updates
                $unique_reference = "Arrears for Fee ID: {$fee->id}";

                // Determine if this arrears item already exists in the target term
                $targetTermArrears = StudentFee::where('student_id', $student_id)
                    ->where('session_id', $new_session_id)
                    ->where('semester_id', $new_semester_id)
                    ->where('fee_type', 'addon')
                    ->where('reference', $unique_reference)
                    ->first();

                if (!$targetTermArrears) {
                    // Create a new fee record for the arrears
                    $targetTermArrears = StudentFee::create([
                        'student_id' => $student_id,
                        'fee_head_id' => $fee->fee_head_id,
                        'session_id' => $new_session_id,
                        'semester_id' => $new_semester_id,
                        'fee_type' => 'addon',
                        'reference' => $unique_reference,
                        'amount' => $fee->balance,
                        'amount_paid' => 0,
                        'balance' => $fee->balance,
                        'status' => 'unpaid',
                        'description' => "Arrears carried forward from Fee ID: {$fee->id}"
                    ]);
                } else {
                    // Arrears already exist - UPDATE it to reflect the latest balance
                    \Log::info("Updating existing arrears for student {$student_id}, fee {$fee->id}. Old balance: {$targetTermArrears->balance}, New balance: {$fee->balance}");
                    $targetTermArrears->update([
                        'amount' => $fee->balance,
                        'balance' => $fee->balance,
                    ]);
                }

                // Link the old fee to the new one and update status to reflect it's moved
                $fee->transferred_to_id = $targetTermArrears->id;
                $fee->status = 'transferred';
                $fee->save();
            });
        }
    }
}
