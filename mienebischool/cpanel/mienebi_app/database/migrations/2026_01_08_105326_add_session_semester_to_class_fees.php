<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionSemesterToClassFees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_fees', function (Blueprint $table) {
            $table->foreignId('session_id')->nullable()->after('fee_head_id')->constrained('school_sessions')->onDelete('set null');
            $table->foreignId('semester_id')->nullable()->after('session_id')->constrained('semesters')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_fees', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
}
