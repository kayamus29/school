<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grading_systems', function (Blueprint $table) {
            if (!Schema::hasColumn('grading_systems', 'marks_breakdown')) {
                $table->json('marks_breakdown')->nullable()->after('system_name');
            }
        });
    }

    public function down()
    {
        Schema::table('grading_systems', function (Blueprint $table) {
            if (Schema::hasColumn('grading_systems', 'marks_breakdown')) {
                $table->dropColumn('marks_breakdown');
            }
        });
    }
};
