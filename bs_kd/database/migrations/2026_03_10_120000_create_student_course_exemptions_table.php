<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentCourseExemptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_course_exemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('session_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('course_id');
            $table->string('reason')->nullable();
            $table->unsignedInteger('removed_by')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'student_id', 'course_id'], 'student_course_exemptions_unique');
            $table->index(['session_id', 'course_id', 'student_id'], 'student_course_exemptions_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_course_exemptions');
    }
}
