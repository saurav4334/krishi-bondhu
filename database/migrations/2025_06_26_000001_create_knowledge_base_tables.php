<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * কৃষি জ্ঞানভান্ডার (Knowledge Base): categories, articles and the queue of
 * unanswered farmer questions. The chatbot answers from here first (free) and
 * only falls back to Gemini when nothing matches.
 *
 * Portable search (LIKE-based keyword scoring) — no MySQL FULLTEXT, so it runs
 * on SQLite and MySQL alike. Plain indexes keep lookups fast.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 16)->nullable();
            $table->string('status')->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title');
            $table->text('question');
            $table->text('keywords')->nullable();  // comma/space separated search terms
            $table->text('answer');
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();
        });

        Schema::create('unanswered_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->string('district')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('status')->default('pending'); // pending | answered
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('unanswered_questions');
        Schema::dropIfExists('knowledge_categories');
    }
};
