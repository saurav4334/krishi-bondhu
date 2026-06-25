<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Knowledge Base upgrade:
 *  - Add trusted-source attribution to articles (source_name/url/type).
 *  - Raise the AI chat daily limits from the old 10/3 to 50/10 (only when still
 *    at the old defaults, so an admin's custom values are preserved).
 *
 * Additive and safe to re-run via Laravel's migration tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->string('source_name')->nullable()->after('answer');
            $table->string('source_url')->nullable()->after('source_name');
            $table->string('source_type')->nullable()->after('source_url'); // government | research | conversational | community
        });

        if (Schema::hasTable('ai_settings')) {
            DB::table('ai_settings')->where('daily_limit', 10)->update(['daily_limit' => 50]);
            DB::table('ai_settings')->where('guest_limit', 3)->update(['guest_limit' => 10]);
        }
    }

    public function down(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropColumn(['source_name', 'source_url', 'source_type']);
        });
    }
};
