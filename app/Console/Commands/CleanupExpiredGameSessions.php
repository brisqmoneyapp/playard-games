<?php

namespace App\Console\Commands;

use App\Models\GameSession;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-expired-game-sessions')]
#[Description('Clean up expired Playard game sessions, share links and temporary assets.')]
class CleanupExpiredGameSessions extends Command
{
    public function handle(): int
    {
        $this->info('Starting Playard game session cleanup...');

        $expiredShareLinks = GameSession::query()
            ->whereNull('cleanup_completed_at')
            ->whereNotNull('share_expires_at')
            ->where('share_expires_at', '<', now())
            ->count();

        GameSession::query()
            ->whereNull('cleanup_completed_at')
            ->whereNotNull('share_expires_at')
            ->where('share_expires_at', '<', now())
            ->update([
                'cleanup_completed_at' => now(),
            ]);

        $oldCancelledSessions = GameSession::query()
            ->whereIn('status', ['cancelled'])
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();

        $oldSetupSessions = GameSession::query()
            ->where('status', 'setup')
            ->where('created_at', '<', now()->subDays(2))
            ->delete();

        $this->info($expiredShareLinks . ' expired share links marked as cleaned.');
        $this->info($oldCancelledSessions . ' old cancelled sessions deleted.');
        $this->info($oldSetupSessions . ' abandoned setup sessions deleted.');
        $this->info('Cleanup completed.');

        return self::SUCCESS;
    }
}
