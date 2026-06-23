<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Full template management: per-template voice + language overrides, and the
 * ability to keep multiple templates per feature type (the active one is used
 * when sending). Drops the unique(type) constraint, keeps a plain index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voice_templates', function (Blueprint $table) {
            $table->string('voice_type')->nullable()->after('end_text');     // male|female (null = use global setting)
            $table->string('language_code', 8)->nullable()->after('voice_type'); // null = use global setting
        });

        Schema::table('voice_templates', function (Blueprint $table) {
            $table->dropUnique('voice_templates_type_unique');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('voice_templates', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->unique('type');
        });

        Schema::table('voice_templates', function (Blueprint $table) {
            $table->dropColumn(['voice_type', 'language_code']);
        });
    }
};
