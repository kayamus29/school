<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIsActiveDebtAddTransferLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_fees', function (Blueprint $table) {
            if (Schema::hasColumn('student_fees', 'is_active_debt')) {
                $table->dropColumn('is_active_debt');
            }
            $table->foreignId('transferred_to_id')->nullable()->after('status')->constrained('student_fees')->onDelete('set null');
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
            $table->dropForeign(['transferred_to_id']);
            $table->dropColumn('transferred_to_id');
            $table->boolean('is_active_debt')->default(true)->after('status');
        });
    }
}
