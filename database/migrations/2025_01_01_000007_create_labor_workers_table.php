<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labor_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('mobile', 11);
            $table->string('district');
            $table->string('area')->nullable(); // ইউনিয়ন / উপজেলা
            $table->string('skill_type');
            $table->decimal('daily_wage', 8, 2);
            $table->string('experience')->nullable();
            $table->enum('availability_status', ['available', 'busy'])->default('available');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index('district');
            $table->index('skill_type');
            $table->index('availability_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_workers');
    }
};
