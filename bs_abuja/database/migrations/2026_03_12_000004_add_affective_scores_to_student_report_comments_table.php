<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_report_comments', function (Blueprint $table) {
            $table->json('affective_scores')->nullable()->after('principal_comment');
        });
    }

    public function down()
    {
        Schema::table('student_report_comments', function (Blueprint $table) {
            $table->dropColumn('affective_scores');
        });
    }
};
