<?php

use App\Models\GameSession;
use App\Models\GameTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GameSession::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GameTeam::class)->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};