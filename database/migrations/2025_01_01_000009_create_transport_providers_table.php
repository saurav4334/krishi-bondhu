<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('driver_name');
            $table->string('mobile', 11);
            $table->string('vehicle_type');
            $table->string('vehicle_number')->nullable();
            $table->string('district');
            $table->string('service_area')->nullable();
            $table->decimal('rate_per_km', 8, 2)->nullable();
            $table->enum('availability_status', ['available', 'busy'])->default('available');
            $table->timestamps();

            $table->index('district');
            $table->index('vehicle_type');
            $table->index('availability_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_providers');
    }
};
