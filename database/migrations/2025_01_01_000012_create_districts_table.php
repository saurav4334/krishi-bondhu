<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->string('name');     // English
            $table->string('bn_name');  // Bengali
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('division_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
