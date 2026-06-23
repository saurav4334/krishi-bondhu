<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-row settings for the Protiddhoni voice (Direct TTS + IVR) module.
 * The API token is stored encrypted (model cast) and never exposed in plaintext.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_base_url')->nullable();
            $table->text('api_token')->nullable();              // stored encrypted (model cast)
            $table->string('sender')->nullable();
            $table->string('default_voice')->default('female'); // male | female
            $table->string('language_code', 8)->default('bn');
            $table->boolean('status')->default(false);          // module enabled?
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_settings');
    }
};
