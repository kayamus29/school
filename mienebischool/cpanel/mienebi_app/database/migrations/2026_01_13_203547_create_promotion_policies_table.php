<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('school_sessions')->onDelete('cascade');
            $table->string('calculation_method')->default('cumulative'); // cumulative, weighted_term_3
            $table->decimal('passing_threshold', 5, 2)->default(50.00);
            $table->json('mandatory_course_ids')->nullable();
            $table->string('probation_logic')->default('retain'); // promote_with_tag, retain
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
        Schema::dropIfExists('promotion_policies');
    }
}
