<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInboundEmailsTable extends Migration
{
    public function up()
    {
        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('message_id')->nullable();
            $table->string('mailbox')->default('INBOX');
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->longText('raw_headers')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('synced_by')->nullable();
            $table->timestamps();

            $table->unique(['uid', 'mailbox']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inbound_emails');
    }
}
