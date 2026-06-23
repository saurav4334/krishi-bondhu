<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product listings for the equipment & agri-input marketplace. Independent of
 * the crop trading module (crop_posts) — equipment and crops never mix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->string('unit', 30)->nullable();
            $table->string('condition')->nullable(); // new | used (for machinery/tools)
            $table->string('location');               // district (bn_name)
            $table->string('upazila', 60)->nullable();
            $table->string('mobile', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();      // featured thumbnail (first gallery image)
            $table->string('status')->default('active')->index();
            $table->boolean('featured')->default(false);
            $table->boolean('approved')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_products');
    }
};
