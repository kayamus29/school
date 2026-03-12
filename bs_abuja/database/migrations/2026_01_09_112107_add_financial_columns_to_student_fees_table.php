<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialColumnsToStudentFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->string('fee_type')->default('tuition')->after('semester_id');
            $table->string('reference')->nullable()->after('fee_type');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('amount');
            $table->decimal('balance', 10, 2)->default(0)->after('amount_paid');
            $table->string('status')->default('unpaid')->after('balance');
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
            $table->dropColumn(['fee_type', 'reference', 'amount_paid', 'balance', 'status']);
        });
    }
}
