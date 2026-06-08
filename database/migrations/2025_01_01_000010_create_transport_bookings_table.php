<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pickup_location');
            $table->string('delivery_location');
            $table->string('crop_type')->nullable();
            $table->string('quantity')->nullable();
            $table->date('booking_date')->nullable();
            $table->string('contact_number', 11);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_bookings');
    }
};
