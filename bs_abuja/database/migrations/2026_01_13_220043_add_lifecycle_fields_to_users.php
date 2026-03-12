<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLifecycleFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'deactivated', 'graduated'])->default('active')->after('role');
            $table->timestamp('deactivated_at')->nullable()->after('status');
            $table->unsignedBigInteger('deactivated_by')->nullable()->after('deactivated_at');
            $table->text('deactivation_reason')->nullable()->after('deactivated_by');
            $table->timestamp('graduated_at')->nullable()->after('deactivation_reason');

            $table->foreign('deactivated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropColumn(['status', 'deactivated_at', 'deactivated_by', 'deactivation_reason', 'graduated_at']);
        });
    }
}
