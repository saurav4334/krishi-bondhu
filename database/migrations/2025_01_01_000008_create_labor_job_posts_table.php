<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labor_job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->string('job_type');
            $table->string('location');
            $table->integer('worker_needed')->default(1);
            $table->decimal('wage', 8, 2);
            $table->string('duration')->nullable();
            $table->string('contact_number', 11);
            $table->enum('status', ['open', 'filled', 'closed'])->default('open');
            $table->timestamps();

            $table->index('farmer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_job_posts');
    }
};
