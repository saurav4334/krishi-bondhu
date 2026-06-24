<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * কৃষি AI সহকারী — floating chatbot. Stores every Q&A for audit/analytics and
 * a single-row settings table (enable/disable + daily question limits).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();          // for guest rate-limit / analytics
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('provider')->default('gemini');
            $table->string('model')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->string('status')->default('success');  // success | failed | simulated
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);       // AI chat enabled?
            $table->unsignedInteger('daily_limit')->default(10);   // per logged-in user / day
            $table->unsignedInteger('guest_limit')->default(3);    // per guest IP / day
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
        Schema::dropIfExists('ai_settings');
    }
};
