<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialColumnsToStudentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_payments', function (Blueprint $table) {
            $table->foreignId('student_fee_id')->nullable()->after('student_id')->constrained('student_fees')->onDelete('set null');
            $table->string('payment_method')->nullable()->after('amount_paid');
            $table->foreignId('received_by')->nullable()->after('reference_no')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_payments', function (Blueprint $table) {
            $table->dropForeign(['student_fee_id']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['student_fee_id', 'payment_method', 'received_by']);
        });
    }
}
