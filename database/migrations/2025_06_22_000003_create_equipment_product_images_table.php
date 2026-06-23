<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gallery images for equipment products (one featured + multiple gallery shots).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_product_id')->index();
            $table->string('image');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_product_images');
    }
};
