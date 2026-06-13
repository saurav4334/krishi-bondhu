<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand the existing crop marketplace with categories, featuring and an
     * admin approval flag. Column is a plain nullable FK id (no DB-level
     * constraint) to stay compatible with SQLite ALTER TABLE.
     *
     * `approved` defaults to true so all existing listings stay visible;
     * new listings created through the expanded form start unapproved.
     */
    public function up(): void
    {
        Schema::table('crop_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
            $table->boolean('featured')->default(false)->after('status');
            $table->boolean('approved')->default(true)->after('featured');
            $table->index('category_id');
            $table->index('approved');
        });
    }

    public function down(): void
    {
        Schema::table('crop_posts', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['approved']);
            $table->dropColumn(['category_id', 'featured', 'approved']);
        });
    }
};
