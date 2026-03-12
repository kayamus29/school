<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_course_exclusions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('removed_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id', 'session_id'], 'student_course_exclusions_unique');
            $table->index(['class_id', 'section_id', 'session_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_course_exclusions');
    }
};
