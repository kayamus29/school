<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialWithholdingToAcademicSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_settings', function (Blueprint $table) {
            $table->boolean('enable_financial_withholding')->default(false)->after('marks_breakdown');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_settings', function (Blueprint $table) {
            $table->dropColumn('enable_financial_withholding');
        });
    }
}
