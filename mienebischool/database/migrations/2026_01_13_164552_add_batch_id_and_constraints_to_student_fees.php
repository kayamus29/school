<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchIdAndConstraintsToStudentFees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('student_fees', 'billing_batch_id')) {
                $table->unsignedBigInteger('billing_batch_id')->nullable()->after('id');
                $table->foreign('billing_batch_id')->references('id')->on('billing_batches')->onDelete('set null');
            }

            // 3-Layer Defense Layer 1: Database Level Uniqueness
            // Check if index exists first (SQLite/MySQL specific checks can be complex, using try-catch or explicit check)
            try {
                $table->unique(['student_id', 'fee_head_id', 'session_id', 'semester_id'], 'unique_student_fee_per_term');
            } catch (\Exception $e) {
                // Index might already exist
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
        Schema::table('student_fees', function (Blueprint $table) {
            //
        });
    }
}
