<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_post_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->timestamps();

            $table->index('crop_post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_post_images');
    }
};
