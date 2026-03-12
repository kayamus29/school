<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentReportCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_report_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedInteger('semester_id');
            $table->unsignedInteger('session_id');
            $table->text('teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            $table->timestamps();

            // Unique constraint to ensure one comment record per student per semester per session
            $table->unique(['student_id', 'semester_id', 'session_id'], 'src_student_semester_session_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_report_comments');
    }
}
