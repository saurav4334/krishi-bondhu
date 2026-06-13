<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mobile');                 // recipient(s); comma-joined for bulk
            $table->text('message');
            $table->string('purpose')->default('general'); // otp_register, otp_login, otp_reset, weather, news, marketplace, broadcast, test
            $table->text('response')->nullable();     // raw gateway response
            $table->enum('status', ['sent', 'failed', 'simulated'])->default('simulated');
            $table->unsignedInteger('recipients')->default(1);
            $table->timestamps();

            $table->index('purpose');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
