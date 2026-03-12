<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExpensesTableForWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->decimal('initial_amount', 15, 2)->nullable()->after('amount');
            $table->text('initial_description')->nullable()->after('description');
            $table->unsignedBigInteger('approver_id')->nullable()->after('expense_date');
            $table->text('approver_notes')->nullable()->after('approver_id');
            $table->string('status')->default('pending')->after('approver_notes'); // pending, approved, rejected, corrected

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approver_id']);
            $table->dropColumn(['user_id', 'initial_amount', 'initial_description', 'approver_id', 'approver_notes', 'status']);
        });
    }
}
