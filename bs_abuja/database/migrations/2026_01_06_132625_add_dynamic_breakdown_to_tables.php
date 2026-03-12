<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDynamicBreakdownToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_settings', 'marks_breakdown')) {
                $table->json('marks_breakdown')->nullable();
            }
        });

        Schema::table('exam_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_rules', 'marks_breakdown')) {
                $table->json('marks_breakdown')->nullable();
            }
        });

        Schema::table('marks', function (Blueprint $table) {
            if (!Schema::hasColumn('marks', 'breakdown_marks')) {
                $table->json('breakdown_marks')->nullable();
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
        Schema::table('academic_settings', function (Blueprint $table) {
            $table->dropColumn('marks_breakdown');
        });

        Schema::table('exam_rules', function (Blueprint $table) {
            $table->dropColumn('marks_breakdown');
        });

        Schema::table('marks', function (Blueprint $table) {
            $table->dropColumn('breakdown_marks');
        });
    }
}
