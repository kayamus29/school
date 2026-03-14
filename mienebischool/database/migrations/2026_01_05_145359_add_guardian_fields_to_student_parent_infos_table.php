<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuardianFieldsToStudentParentInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_parent_infos', function (Blueprint $table) {
            $table->string('guardian_email')->nullable()->after('mother_phone');
            $table->string('guardian_phone')->nullable()->after('guardian_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_parent_infos', function (Blueprint $table) {
            $table->dropColumn(['guardian_email', 'guardian_phone']);
        });
    }
}
