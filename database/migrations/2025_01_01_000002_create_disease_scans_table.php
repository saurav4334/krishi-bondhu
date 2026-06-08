<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->string('disease_name');
            $table->integer('confidence_score');
            $table->text('symptoms')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prevention')->nullable();
            $table->json('ai_result')->nullable();
            $table->enum('status', ['processing', 'completed', 'failed'])->default('completed');
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_scans');
    }
};
