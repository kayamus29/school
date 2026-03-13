<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    /**
     * Helper to check if an index exists on a table.
     *
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        // This is a raw query fallback for older Laravel versions
        // that don't have Schema::hasIndex.
        $indexs = DB::select("SHOW INDEX FROM {$tableName} WHERE Key_name = '{$indexName}'");
        return count($indexs) > 0;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // It's safe to run this migration even if indexes exist.
        // We check schema before adding to avoid errors on re-runs.

        Schema::table('wallets', function (Blueprint $table) {
            // Wallets are almost always looked up by student. This should be unique.
            if (!$this->indexExists('wallets', 'wallets_student_id_unique')) {
                $table->unique('student_id', 'wallets_student_id_unique');
            }
        });

        Schema::table('promotions', function (Blueprint $table) {
            // This composite index is critical for finding a student's current class in a session.
            if (!$this->indexExists('promotions', 'promotions_student_session_composite')) {
                $table->index(['student_id', 'session_id'], 'promotions_student_session_composite');
            }
            // This is used for finding all students in a class for a session (e.g., billing preview).
            if (!$this->indexExists('promotions', 'promotions_session_class_composite')) {
                $table->index(['session_id', 'class_id'], 'promotions_session_class_composite');
            }
        });

        Schema::table('student_fees', function (Blueprint $table) {
            // For finding all fees for a student.
            if (!$this->indexExists('student_fees', 'student_fees_student_id_index')) {
                $table->index('student_id', 'student_fees_student_id_index');
            }
            // For finding arrears and other session-specific fees.
            if (!$this->indexExists('student_fees', 'student_fees_session_id_index')) {
                $table->index('session_id', 'student_fees_session_id_index');
            }
            // For idempotency checks on carry-forward arrears.
            if (!$this->indexExists('student_fees', 'student_fees_reference_index')) {
                $table->index('reference', 'student_fees_reference_index');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            // For retrieving a wallet's history.
            if (!$this->indexExists('wallet_transactions', 'wallet_transactions_wallet_id_index')) {
                $table->index('wallet_id', 'wallet_transactions_wallet_id_index');
            }
            // For finding the transaction related to a specific model (e.g., StudentFee, Payment).
            if (!$this->indexExists('wallet_transactions', 'wallet_transactions_reference_poly_composite')) {
                $table->index(['reference_type', 'reference_id'], 'wallet_transactions_reference_poly_composite');
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
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropUnique('wallets_student_id_unique');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex('promotions_student_session_composite');
            $table->dropIndex('promotions_session_class_composite');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropIndex('student_fees_student_id_index');
            $table->dropIndex('student_fees_session_id_index');
            $table->dropIndex('student_fees_reference_index');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('wallet_transactions_wallet_id_index');
            $table->dropIndex('wallet_transactions_reference_poly_composite');
        });
    }
}