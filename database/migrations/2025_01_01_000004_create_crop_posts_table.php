<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('crop_name');
            $table->string('quantity', 50);
            $table->decimal('price', 10, 2);
            $table->string('location');
            $table->string('mobile', 11);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'sold', 'deleted'])->default('active');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_posts');
    }
};
