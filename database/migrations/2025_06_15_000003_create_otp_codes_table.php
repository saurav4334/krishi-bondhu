<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 11);
            $table->string('otp', 6);
            $table->string('purpose')->default('login'); // register, login, password_reset
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['mobile', 'purpose']);
            $table->index('created_at'); // for hourly rate-limit counting
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
