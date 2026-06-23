<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categories for the standalone "কৃষি সরঞ্জাম" (equipment & agri-input) marketplace.
 * Self-referencing parent_id supports main categories + subcategories.
 * Plain nullable FK id (no DB-level constraint) for SQLite compatibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 16)->nullable();
            $table->string('status')->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};
