<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultWeightsToAcademicSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_settings', 'default_exam_weight')) {
                $table->integer('default_exam_weight')->default(70);
            }
            if (!Schema::hasColumn('academic_settings', 'default_ca1_weight')) {
                $table->integer('default_ca1_weight')->default(15);
            }
            if (!Schema::hasColumn('academic_settings', 'default_ca2_weight')) {
                $table->integer('default_ca2_weight')->default(15);
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
            $table->dropColumn(['default_exam_weight', 'default_ca1_weight', 'default_ca2_weight']);
        });
    }
}
