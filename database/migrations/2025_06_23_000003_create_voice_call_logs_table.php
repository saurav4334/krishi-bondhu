<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every outbound voice call: the exact request payload + API response, the
 * captured DTMF key, status and retry counter (cron-driven, max 3 retries).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->string('feature_type')->default('general'); // weather_alert, crop_lead, ...
            $table->unsignedBigInteger('related_id')->nullable(); // alert/post/job id that triggered it
            $table->string('request_id')->unique();
            $table->text('payload')->nullable();      // JSON request body sent (no token)
            $table->text('api_response')->nullable(); // raw API response
            $table->string('dtmf_key')->nullable();   // captured keypad response
            $table->string('call_status')->default('queued'); // queued|sent|failed|simulated
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            $table->index('feature_type');
            $table->index('call_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_call_logs');
    }
};
