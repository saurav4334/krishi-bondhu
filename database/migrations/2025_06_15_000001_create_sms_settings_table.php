<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->text('api_key')->nullable();          // stored encrypted (model cast)
            $table->string('sender_id')->nullable();
            $table->enum('sms_type', ['text', 'unicode'])->default('unicode'); // Bengali => unicode
            $table->enum('label', ['transactional', 'promotional'])->default('transactional');
            $table->boolean('status')->default(false);     // SMS module enabled?
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
