<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_summary_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('session_id');
            $table->integer('days_present')->default(0);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id', 'session_id'], 'attendance_summary_overrides_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_summary_overrides');
    }
};
