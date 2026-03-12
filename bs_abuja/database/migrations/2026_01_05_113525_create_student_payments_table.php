<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            // Assuming we track payments against a Class (Grade) and Session/Term
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            // Using integer/string for session/semester IDs as standard foreign keys might be complex without looking up exact table names, but `school_sessions` and `semesters` likely exist.
            // Based on route analysis: `SchoolSessionController` and `SemesterController` exist.
            $table->unsignedBigInteger('school_session_id'); // foreign key linked manually or via relation
            $table->unsignedBigInteger('semester_id'); // foreign key linked manually or via relation

            $table->decimal('amount_paid', 10, 2);
            $table->date('transaction_date');
            $table->string('reference_no')->nullable(); // Receipt number or bank ref
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_payments');
    }
}
