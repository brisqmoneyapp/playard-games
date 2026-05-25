<?php

namespace App\Events;

use App\Models\GameSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameSessionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public GameSession $session
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('lane.' . $this->session->game_resource_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'game.session.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'status' => $this->session->status,
            'resource_id' => $this->session->game_resource_id,
            'duration_minutes' => $this->session->duration_minutes,
            'started_at' => $this->session->started_at,
            'ended_at' => $this->session->ended_at,
        ];
    }
}
