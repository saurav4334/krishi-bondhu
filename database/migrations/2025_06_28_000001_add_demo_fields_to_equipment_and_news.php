<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Demo-data support:
 *  - equipment_products.is_demo  → so the demo seeder can wipe only its own
 *    products and never touch real/manually-added ones.
 *  - news_posts: content (full article), views_count, is_demo.
 *
 * Existing seeded demo equipment (mobile 0171100…) is flagged is_demo so the
 * rewritten seeder cleans it up. Additive + safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_products', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('approved');
        });

        // Flag previously-seeded demo products (they used 0171100xxxx numbers).
        DB::table('equipment_products')->where('mobile', 'like', '0171100%')->update(['is_demo' => true]);

        Schema::table('news_posts', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('description');
            $table->unsignedInteger('views_count')->default(0)->after('content');
            $table->boolean('is_demo')->default(false)->after('is_important');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_products', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
        Schema::table('news_posts', function (Blueprint $table) {
            $table->dropColumn(['content', 'views_count', 'is_demo']);
        });
    }
};
