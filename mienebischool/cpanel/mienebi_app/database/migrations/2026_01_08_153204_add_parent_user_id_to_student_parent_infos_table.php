<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentUserIdToStudentParentInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_parent_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_user_id')->nullable()->after('student_id');
            $table->foreign('parent_user_id')->references('id')->on('users')->onDelete('cascade');
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
            $table->dropForeign(['parent_user_id']);
            $table->dropColumn('parent_user_id');
        });
    }
}
