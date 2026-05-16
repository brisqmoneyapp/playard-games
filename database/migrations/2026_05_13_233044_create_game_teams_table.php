<?php

use App\Models\GameSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GameSession::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('colour')->default('red');
            $table->integer('total_score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_teams');
    }
};