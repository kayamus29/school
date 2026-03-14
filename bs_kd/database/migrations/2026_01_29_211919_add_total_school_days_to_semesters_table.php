<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalSchoolDaysToSemestersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('semesters', 'total_school_days')) {
            Schema::table('semesters', function (Blueprint $table) {
                $table->integer('total_school_days')->nullable()->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('semesters', 'total_school_days')) {
            Schema::table('semesters', function (Blueprint $table) {
                $table->dropColumn('total_school_days');
            });
        }
    }
}
