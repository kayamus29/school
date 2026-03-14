<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateGradingSystemsAndMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Pivot table for multi-class grading system
        if (!Schema::hasTable('class_grading_system')) {
            Schema::create('class_grading_system', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('class_id');
                $table->unsignedBigInteger('grading_system_id');
                $table->timestamps();
            });
        }

        // 2. Add weight fields to exam_rules
        Schema::table('exam_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_rules', 'exam_weight')) {
                $table->float('exam_weight')->default(70);
            }
            if (!Schema::hasColumn('exam_rules', 'ca1_weight')) {
                $table->float('ca1_weight')->default(15);
            }
            if (!Schema::hasColumn('exam_rules', 'ca2_weight')) {
                $table->float('ca2_weight')->default(15);
            }
        });

        // 3. Add breakdown mark fields to marks
        Schema::table('marks', function (Blueprint $table) {
            if (!Schema::hasColumn('marks', 'exam_mark')) {
                $table->float('exam_mark')->nullable()->default(0);
            }
            if (!Schema::hasColumn('marks', 'ca1_mark')) {
                $table->float('ca1_mark')->nullable()->default(0);
            }
            if (!Schema::hasColumn('marks', 'ca2_mark')) {
                $table->float('ca2_mark')->nullable()->default(0);
            }
        });

        // 4. Make grading_systems.class_id nullable using raw SQL
        try {
            DB::statement('ALTER TABLE grading_systems MODIFY class_id INT UNSIGNED NULL');
        } catch (\Exception $e) {
            // Log or ignore
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_grading_system');

        Schema::table('exam_rules', function (Blueprint $table) {
            $table->dropColumn(['exam_weight', 'ca1_weight', 'ca2_weight']);
        });

        Schema::table('marks', function (Blueprint $table) {
            $table->dropColumn(['exam_mark', 'ca1_mark', 'ca2_mark']);
        });

        DB::statement('ALTER TABLE grading_systems MODIFY class_id INT UNSIGNED NOT NULL');
    }
}
