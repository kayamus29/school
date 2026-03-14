<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationsTables extends Migration
{
    public function up()
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20);
            $table->string('audience_type', 20);
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('sender_role', 50)->nullable();
            $table->string('subject')->nullable();
            $table->longText('message');
            $table->longText('message_html')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('successful_recipients')->default(0);
            $table->unsignedInteger('failed_recipients')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('communication_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('channel', 20);
            $table->string('recipient_name')->nullable();
            $table->string('destination');
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['communication_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('communication_recipients');
        Schema::dropIfExists('communications');
    }
}
