<?php

namespace App\Services;

use App\Interfaces\WalletServiceInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService implements WalletServiceInterface
{
    /**
     * Get the current wallet balance for a student.
     */
    public function getBalance(int $studentId): float
    {
        $wallet = DB::table('wallets')->where('student_id', $studentId)->first();
        if (!$wallet) {
            // Lazy create if missing, though migration should have covered it
            $id = DB::table('wallets')->insertGetId([
                'student_id' => $studentId,
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return 0.00;
        }
        return (float) $wallet->balance;
    }

    /**
     * Record a deposit (User paying money).
     */
    public function deposit(int $studentId, float $amount, string $referenceType, int $referenceId, ?string $description = null): void
    {
        $this->transact($studentId, $amount, 'deposit', $referenceType, $referenceId, $description);
    }

    /**
     * Charge a fee (Invoice created).
     */
    public function charge(int $studentId, float $amount, string $referenceType, int $referenceId, ?string $description = null): void
    {
        // Charges are negative impacts on the wallet
        $chargeAmount = -1 * abs($amount);
        $this->transact($studentId, $chargeAmount, 'fee_charge', $referenceType, $referenceId, $description);
    }

    /**
     * Credit for a transfer (Neutralizing duplicate debt).
     */
    public function creditTransfer(int $studentId, float $amount, string $referenceType, int $referenceId, ?string $description = null): void
    {
        $this->transact($studentId, abs($amount), 'transfer_credit', $referenceType, $referenceId, $description);
    }

    /**
     * Admin adjustment (Correction).
     */
    public function adjust(int $studentId, float $amount, string $reason): void
    {
        $this->transact($studentId, $amount, 'adjustment', null, 0, $reason);
    }

    /**
     * Core Transaction Logic
     */
    private function transact(int $studentId, float $amount, string $type, ?string $refType, int $refId, ?string $desc): void
    {
        DB::transaction(function () use ($studentId, $amount, $type, $refType, $refId, $desc) {
            $wallet = DB::table('wallets')->where('student_id', $studentId)->lockForUpdate()->first();

            if (!$wallet) {
                $walletId = DB::table('wallets')->insertGetId([
                    'student_id' => $studentId,
                    'balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $currentBalance = 0;
            } else {
                $walletId = $wallet->id;
                $currentBalance = (float) $wallet->balance;
            }

            $newBalance = $currentBalance + $amount;

            // 1. Create Transaction Record
            DB::table('wallet_transactions')->insert([
                'wallet_id' => $walletId,
                'amount' => $amount,
                'type' => $type,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'running_balance' => $newBalance,
                'description' => $desc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Update Wallet Cache
            DB::table('wallets')->where('id', $walletId)->update([
                'balance' => $newBalance,
                'updated_at' => now()
            ]);
        });
    }

    /**
     * Verify the wallet balance matches the sum of transactions.
     */
    public function checkInvariant(int $studentId): bool
    {
        $wallet = DB::table('wallets')->where('student_id', $studentId)->first();
        if (!$wallet)
            return true; // Empty is consistent

        $sum = DB::table('wallet_transactions')->where('wallet_id', $wallet->id)->sum('amount');

        // Floating point comparison
        return abs($wallet->balance - $sum) < 0.01;
    }
}
