<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('end_term_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('title')->nullable();
            $table->string('content_format')->default('plain_text');
            $table->longText('content_body')->nullable();
            $table->string('newsletter_url')->nullable();
            $table->string('next_term_label')->nullable();
            $table->date('next_resumption_date')->nullable();
            $table->date('fee_deadline')->nullable();
            $table->text('resumption_note')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'semester_id']);
            $table->index('session_id');
            $table->index('semester_id');
            $table->index('published_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('end_term_updates');
    }
};
