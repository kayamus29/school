<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeCourseAndSectionNullableInAssignedTeachers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assigned_teachers', function (Blueprint $table) {
            // Using DB query to bypass doctrine/dbal dependency
            DB::statement("ALTER TABLE assigned_teachers MODIFY course_id INT UNSIGNED NULL");
            DB::statement("ALTER TABLE assigned_teachers MODIFY section_id INT UNSIGNED NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assigned_teachers', function (Blueprint $table) {
            DB::statement("ALTER TABLE assigned_teachers MODIFY course_id INT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE assigned_teachers MODIFY section_id INT UNSIGNED NOT NULL");
        });
    }
}
