<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create Wallets Table
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0); // Cached Snapshot
            $table->timestamps();
        });

        // 2. Create Wallet Transactions Table
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Signed: + for Deposit, - for Charge
            $table->string('type'); // deposit, fee_charge, transfer_credit, adjustment
            $table->nullableMorphs('reference'); // Reference to Payment or Fee
            $table->decimal('running_balance', 15, 2)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Backfill Data (Transactional Safety)
        DB::transaction(function () {
            $students = User::where('role', 'student')->get();
            $errors = [];

            foreach ($students as $student) {
                // Create Wallet
                $walletId = DB::table('wallets')->insertGetId([
                    'student_id' => $student->id,
                    'balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $runningBalance = 0;

                // A. Process Payments (Deposits)
                // Order by date to simulate timeline related to fees
                $payments = DB::table('student_payments')
                    ->where('student_id', $student->id)
                    ->orderBy('created_at')
                    ->get();

                foreach ($payments as $payment) {
                    $amount = $payment->amount_paid;
                    $runningBalance += $amount;

                    DB::table('wallet_transactions')->insert([
                        'wallet_id' => $walletId,
                        'amount' => $amount,
                        'type' => 'deposit',
                        'reference_type' => 'App\Models\StudentPayment',
                        'reference_id' => $payment->id,
                        'running_balance' => $runningBalance,
                        'description' => 'Historical Payment Ref: ' . $payment->reference_no,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->created_at,
                    ]);
                }

                // B. Process Fees (Charges)
                // We process ALL fees.
                $fees = DB::table('student_fees')
                    ->where('student_id', $student->id)
                    ->orderBy('created_at')
                    ->get();

                foreach ($fees as $fee) {
                    // Charge the Fee
                    $chargeAmount = -1 * abs($fee->amount);
                    $runningBalance += $chargeAmount;

                    DB::table('wallet_transactions')->insert([
                        'wallet_id' => $walletId,
                        'amount' => $chargeAmount,
                        'type' => 'fee_charge',
                        'reference_type' => 'App\Models\StudentFee',
                        'reference_id' => $fee->id,
                        'running_balance' => $runningBalance,
                        'description' => $fee->description ?? ('Fee Charge: ' . $fee->fee_type),
                        'created_at' => $fee->created_at,
                        'updated_at' => $fee->created_at,
                    ]);

                    // C. Handle Transfers (Neutralize Duplicate Debt)
                    if ($fee->transferred_to_id) {
                        // The 'balance' at moment of transfer is what we credit back.
                        // We approximate this using (amount - amount_paid).
                        // Note: historical 'balance' column on student_fees might be accurate.
                        // Using (amount - amount_paid) is safer if balance wasn't maintained perfectly.

                        $creditAmount = $fee->amount - $fee->amount_paid;

                        if ($creditAmount > 0) {
                            $runningBalance += $creditAmount;

                            DB::table('wallet_transactions')->insert([
                                'wallet_id' => $walletId,
                                'amount' => $creditAmount,
                                'type' => 'transfer_credit',
                                'reference_type' => 'App\Models\StudentFee',
                                'reference_id' => $fee->id,
                                'running_balance' => $runningBalance,
                                'description' => 'Transfer Credit (Neutralize Duplicate Arrears)',
                                'created_at' => $fee->updated_at, // Use updated_at as transfer time
                                'updated_at' => $fee->updated_at,
                            ]);
                        }
                    }
                }

                // Update Final Wallet Balance
                DB::table('wallets')->where('id', $walletId)->update(['balance' => $runningBalance]);

                // 4. Invariant Verification (Internal Consistency)
                // We verify that the Wallet Balance exactly matches (Total Payments - Total Fees + Total Transfers).
                // This ensures we didn't miss any records during the loop.

                $totalPayments = DB::table('student_payments')->where('student_id', $student->id)->sum('amount_paid');
                $totalFees = DB::table('student_fees')->where('student_id', $student->id)->sum('amount'); // fees are positive in table

                // Calculate expected transfer credits
                $totalTransferCredit = DB::table('student_fees')
                    ->where('student_id', $student->id)
                    ->whereNotNull('transferred_to_id')
                    ->get()
                    ->sum(function ($f) {
                        return $f->amount - $f->amount_paid; });

                $expectedFormulaBalance = $totalPayments - $totalFees + $totalTransferCredit;

                if (abs($runningBalance - $expectedFormulaBalance) > 0.01) {
                    $errors[] = "Calculation Error for Student {$student->id}: Wallet={$runningBalance}, Expected={$expectedFormulaBalance}";
                }
            }

            if (count($errors) > 0) {
                throw new Exception("Migration Logic Failed (Internal Inconsistency):\n" . implode("\n", $errors));
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
}
