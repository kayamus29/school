<?php

namespace App\Interfaces;

interface WalletServiceInterface
{
    /**
     * Get the current wallet balance for a student.
     * 
     * @param int $studentId
     * @return float
     */
    public function getBalance(int $studentId): float;

    /**
     * Record a deposit (User paying money).
     * 
     * @param int $studentId
     * @param float $amount
     * @param string $referenceType
     * @param int $referenceId
     * @param string|null $description
     * @return void
     */
    public function deposit(int $studentId, float $amount, string $referenceType, int $referenceId, ?string $description = null): void;

    /**
     * Charge a fee (Invoice created).
     * 
     * @param int $studentId
     * @param float $amount
     * @param string $referenceType
     * @param int $referenceId
     * @param string|null $description
     * @return void
     */
    public function charge(int $studentId, float $amount, string $referenceType, int $referenceId, ?string $description = null): void;

    /**
     * Credit for a transfer (Neutralizing duplicate debt).
     * 
     * @param int $studentId
     * @param float $amount
     * @param string $referenceType
     * @param int $referenceId
     * @param string|null $description
     * @return void
     */
    public function creditTransfer(int $studentId, float $amount, string $referenceType, int $referenceId, ?string $description = null): void;

    /**
     * Admin adjustment (Correction).
     * 
     * @param int $studentId
     * @param float $amount Signed amount (+ to credit wallet, - to debit)
     * @param string $reason
     * @return void
     */
    public function adjust(int $studentId, float $amount, string $reason): void;

    /**
     * Verify the wallet balance matches the sum of transactions.
     * 
     * @param int $studentId
     * @return bool
     */
    public function checkInvariant(int $studentId): bool;
}
