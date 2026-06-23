<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Follow-up actions a farmer requested by keypad (e.g. "2 = expert callback",
 * "1 = send buyer info") so admins/experts can action them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_callback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feature_type');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('phone', 20);
            $table->string('status')->default('pending'); // pending|done
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_callback_requests');
    }
};
