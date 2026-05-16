<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->timestamp('share_expires_at')->nullable()->after('share_code');
            $table->timestamp('temporary_assets_expire_at')->nullable()->after('share_expires_at');
            $table->timestamp('cleanup_completed_at')->nullable()->after('temporary_assets_expire_at');
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'share_expires_at',
                'temporary_assets_expire_at',
                'cleanup_completed_at',
            ]);
        });
    }
};
