<?php

namespace App\Services;

use App\Models\StudentFee;
use Illuminate\Support\Facades\DB;

class StudentLedgerSyncService
{
    /**
     * Recalculate fee balances from the student's wallet position.
     * The wallet remains the source of truth; fee rows are synced oldest-first.
     */
    public function syncStudent(int $studentId): void
    {
        DB::transaction(function () use ($studentId) {
            $fees = StudentFee::where('student_id', $studentId)
                ->whereNull('transferred_to_id')
                ->orderByRaw('COALESCE(created_at, updated_at) asc')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($fees->isEmpty()) {
                return;
            }

            $wallet = DB::table('wallets')
                ->where('student_id', $studentId)
                ->lockForUpdate()
                ->first();

            $walletBalance = $wallet ? (float) $wallet->balance : 0.0;
            $totalFeeAmount = (float) $fees->sum('amount');

            // Covered amount is whatever portion of billed fees is not represented by wallet debt.
            $coveredAmount = max(0, min($totalFeeAmount, $totalFeeAmount + $walletBalance));

            foreach ($fees as $fee) {
                $allocated = min((float) $fee->amount, $coveredAmount);
                $balance = max(0, (float) $fee->amount - $allocated);

                $fee->amount_paid = $allocated;
                $fee->balance = $balance;
                $fee->status = $balance <= 0
                    ? 'paid'
                    : ($allocated > 0 ? 'partial' : 'unpaid');
                $fee->save();

                $coveredAmount -= $allocated;
            }
        });
    }
}
