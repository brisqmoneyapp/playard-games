<?php

use App\Models\GameSession;
use App\Models\GameTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GameSession::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->foreignIdFor(GameTeam::class, 'winning_team_id')->nullable()->constrained('game_teams')->nullOnDelete();
            $table->unsignedInteger('points')->default(0);
            $table->text('commentary')->nullable();
            $table->timestamps();

            $table->unique(['game_session_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};