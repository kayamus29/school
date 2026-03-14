<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('student_report_comments', 'affective_scores')) {
            Schema::table('student_report_comments', function (Blueprint $table) {
                $table->json('affective_scores')->nullable()->after('principal_comment');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('student_report_comments', 'affective_scores')) {
            Schema::table('student_report_comments', function (Blueprint $table) {
                $table->dropColumn('affective_scores');
            });
        }
    }
};
