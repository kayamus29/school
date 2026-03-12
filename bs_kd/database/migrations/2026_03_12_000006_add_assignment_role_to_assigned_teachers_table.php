<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assigned_teachers', function (Blueprint $table) {
            $table->string('assignment_role')->nullable()->after('course_id');
        });

        DB::table('assigned_teachers')
            ->whereNull('assignment_role')
            ->update([
                'assignment_role' => DB::raw("CASE WHEN course_id IS NULL THEN 'section_teacher' ELSE 'subject_teacher' END"),
            ]);
    }

    public function down(): void
    {
        Schema::table('assigned_teachers', function (Blueprint $table) {
            $table->dropColumn('assignment_role');
        });
    }
};
