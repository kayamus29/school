<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $row) {
            $row->boolean('is_active_debt')->default(true)->after('status');
        });
    }

    /**
     * Down the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $row) {
            $row->dropColumn('is_active_debt');
        });
    }
};
