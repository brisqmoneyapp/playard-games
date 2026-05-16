<?php

use App\Models\Activity;
use App\Models\GameResource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Activity::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GameResource::class)->constrained()->cascadeOnDelete();
            $table->string('status')->default('setup');
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('share_code')->unique();
            $table->string('winner_team_name')->nullable();
            $table->integer('team_one_total')->default(0);
            $table->integer('team_two_total')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};