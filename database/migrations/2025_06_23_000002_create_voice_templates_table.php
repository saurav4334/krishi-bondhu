<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable Bengali voice templates per feature. Texts support dynamic variables
 * {{name}} {{district}} {{crop}} {{product}} {{service}} {{date}}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();   // weather_alert, crop_lead, equipment_rental, labor_match, govt_circular
            $table->string('title');
            $table->text('start_text')->nullable();
            $table->text('question_text');
            $table->text('end_text')->nullable();
            $table->json('dtmf_options')->nullable(); // [{key, option_type, texts:[]}]
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_templates');
    }
};
