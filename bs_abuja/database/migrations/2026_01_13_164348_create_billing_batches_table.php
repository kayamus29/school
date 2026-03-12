<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('school_sessions');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->foreignId('processed_by')->constrained('users');
            $table->integer('student_count')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['in_progress', 'finalized', 'locked'])->default('in_progress');
            $table->json('batch_meta')->nullable();
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
        Schema::dropIfExists('billing_batches');
    }
}
