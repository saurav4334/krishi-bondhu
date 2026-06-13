<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('district', 50);
            $table->enum('alert_type', ['heavy_rain', 'flood', 'heat_wave', 'thunderstorm']);
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'moderate', 'high', 'severe'])->default('moderate');
            $table->date('alert_date');
            $table->timestamps();

            $table->index(['district', 'alert_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_alerts');
    }
};
