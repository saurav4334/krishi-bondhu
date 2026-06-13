<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Equipment-sale support: item condition (new/used) and an upazila field
     * alongside the existing `location` (district). Both nullable so existing
     * crop listings are unaffected.
     */
    public function up(): void
    {
        Schema::table('crop_posts', function (Blueprint $table) {
            $table->string('condition', 10)->nullable()->after('price'); // new | used
            $table->string('upazila', 60)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('crop_posts', function (Blueprint $table) {
            $table->dropColumn(['condition', 'upazila']);
        });
    }
};
