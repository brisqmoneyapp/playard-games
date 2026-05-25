<?php

namespace App\Http\Controllers\Play;

use App\Events\GameSessionUpdated;

use App\Http\Controllers\Controller;
use App\Mail\GameResultsMail;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\GamePlayer;
use App\Models\GameResource;
use App\Models\GameRound;
use App\Models\GameScore;
use App\Models\GameSession;
use App\Models\GameTeam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CurlingController extends Controller
{
    public function staffDashboard(): View
    {
        $activity = Activity::query()->where('slug', 'curling')->firstOrFail();

        $resources = GameResource::query()
            ->with(['activity', 'sessions' => function ($query) {
                $query->whereIn('status', ['setup', 'playing', 'paused'])->latest();
            }])
            ->where('activity_id', $activity->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('play.staff-dashboard', [
            'activity' => $activity,
            'resources' => $resources,
        ]);
    }

    public function startGame(Request $request, GameResource $resource): RedirectResponse
    {
        $validated = $request->validate([
            'duration_minutes' => ['required', 'integer', 'in:30,60'],
        ]);

        GameSession::query()
            ->where('game_resource_id', $resource->id)
            ->whereIn('status', ['setup', 'playing', 'paused'])
            ->update([
                'status' => 'cancelled',
                'ended_at' => now(),
            ]);

        $session = GameSession::create([
            'activity_id' => $resource->activity_id,
            'game_resource_id' => $resource->id,
            'status' => 'setup',
            'duration_minutes' => $validated['duration_minutes'],
            'share_code' => Str::upper(Str::random(8)),
            'share_expires_at' => now()->addDays(30),
            'temporary_assets_expire_at' => now()->addDays(7),
            'metadata' => [
                'created_from' => 'staff_dashboard',
                'retention_policy' => [
                    'share_link_days' => 30,
                    'temporary_asset_days' => 7,
                    'photos_and_replays_hours' => 24,
                ],
            ],
        ]);

        GameSessionUpdated::dispatch($session->fresh());

        return redirect()
            ->route('play.tablet', $resource)
            ->with('success', 'Game launched for ' . $resource->name . '.');
    }

    public function tablet(GameResource $resource): View
    {
        $session = GameSession::query()
            ->with(['activity', 'resource', 'teams.players', 'rounds.winningTeam'])
            ->where('game_resource_id', $resource->id)
            ->whereIn('status', ['setup', 'playing', 'paused', 'finished'])
            ->latest()
            ->first();

        return view('play.curling.tablet', [
            'resource' => $resource->load('activity'),
            'session' => $session,
            'funNames' => $this->funTeamNames(),
        ]);
    }

    public function saveSetup(Request $request, GameSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'team_one_name' => ['required', 'string', 'max:80'],
            'team_two_name' => ['required', 'string', 'max:80'],
            'players' => ['required', 'array', 'min:2'],
            'players.*.name' => ['nullable', 'string', 'max:80'],
            'players.*.team' => ['required', 'in:one,two'],
        ]);

        $session->teams()->delete();
        $session->players()->delete();

        $teamOne = GameTeam::create([
            'game_session_id' => $session->id,
            'name' => $validated['team_one_name'],
            'colour' => 'red',
            'total_score' => 0,
        ]);

        $teamTwo = GameTeam::create([
            'game_session_id' => $session->id,
            'name' => $validated['team_two_name'],
            'colour' => 'yellow',
            'total_score' => 0,
        ]);

        $sort = 1;
        foreach ($validated['players'] as $player) {
            $name = trim($player['name'] ?? '');

            if ($name === '') {
                continue;
            }

            GamePlayer::create([
                'game_session_id' => $session->id,
                'game_team_id' => $player['team'] === 'one' ? $teamOne->id : $teamTwo->id,
                'name' => $name,
                'sort_order' => $sort++,
            ]);
        }

        return redirect()
            ->route('play.tablet', $session->resource)
            ->with('success', 'Teams saved. Start the timer when players are ready.');
    }

    public function startTimer(GameSession $session): RedirectResponse
    {
        if ($session->teams()->count() < 2) {
            return back()->with('error', 'Add teams before starting the game.');
        }

        $session->update([
            'status' => 'playing',
            'started_at' => now(),
            'ends_at' => now()->addMinutes($session->duration_minutes),
        ]);

        GameSessionUpdated::dispatch($session->fresh());

        return redirect()
            ->route('play.tablet', $session->resource)
            ->with('success', 'Timer started. Game on.');
    }

    public function saveScore(Request $request, GameSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'winning_team_id' => ['nullable', 'exists:game_teams,id'],
            'points' => ['required', 'integer', 'min:0', 'max:8'],
        ]);

        $roundNumber = (int) $session->rounds()->max('round_number') + 1;
        $winningTeamId = $validated['winning_team_id'] ?: null;
        $points = $winningTeamId ? (int) $validated['points'] : 0;

        $round = GameRound::create([
            'game_session_id' => $session->id,
            'round_number' => $roundNumber,
            'winning_team_id' => $winningTeamId,
            'points' => $points,
            'commentary' => $this->randomCommentary($points),
        ]);

        if ($winningTeamId && $points > 0) {
            GameScore::create([
                'game_session_id' => $session->id,
                'game_round_id' => $round->id,
                'game_team_id' => $winningTeamId,
                'points' => $points,
            ]);
        }

        $this->recalculateTotals($session->fresh());
        GameSessionUpdated::dispatch($session->fresh());

        return redirect()
            ->route('play.tablet', $session->resource)
            ->with('success', 'Round ' . $roundNumber . ' saved.');
    }

    public function endGame(GameSession $session): RedirectResponse
    {
        $this->finishSession($session);
        GameSessionUpdated::dispatch($session->fresh());

        return redirect()
            ->route('play.tablet', $session->resource)
            ->with('success', 'Game ended. Results are ready.');
    }

    public function resetGame(GameSession $session): RedirectResponse
    {
        $resource = $session->resource;

        $session->update([
            'status' => 'cancelled',
            'ended_at' => now(),
        ]);

        return redirect()
            ->route('staff.dashboard')
            ->with('success', $resource->name . ' has been reset.');
    }

    public function pauseGame(GameSession $session): RedirectResponse
    {
        if ($session->status !== 'playing') {
            return back()->with('error', 'Only a playing game can be paused.');
        }

        $remainingSeconds = $session->ends_at
            ? max(0, now()->diffInSeconds($session->ends_at, false))
            : ($session->duration_minutes * 60);

        $metadata = $session->metadata ?? [];
        $metadata['remaining_seconds_when_paused'] = $remainingSeconds;
        $metadata['paused_at'] = now()->toIso8601String();

        $session->update([
            'status' => 'paused',
            'metadata' => $metadata,
        ]);

        GameSessionUpdated::dispatch($session->fresh());

        return back()->with('success', 'Game paused.');
    }

    public function resumeGame(GameSession $session): RedirectResponse
    {
        if ($session->status !== 'paused') {
            return back()->with('error', 'Only a paused game can be resumed.');
        }

        $metadata = $session->metadata ?? [];
        $remainingSeconds = (int) ($metadata['remaining_seconds_when_paused'] ?? ($session->duration_minutes * 60));

        unset($metadata['paused_at'], $metadata['remaining_seconds_when_paused']);

        $session->update([
            'status' => 'playing',
            'ends_at' => now()->addSeconds($remainingSeconds),
            'metadata' => $metadata,
        ]);

        GameSessionUpdated::dispatch($session->fresh());

        return back()->with('success', 'Game resumed.');
    }

    public function addTime(Request $request, GameSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'minutes' => ['required', 'integer', 'in:10,30'],
        ]);

        $minutes = (int) $validated['minutes'];
        $metadata = $session->metadata ?? [];

        if ($session->status === 'paused') {
            $remainingSeconds = (int) ($metadata['remaining_seconds_when_paused'] ?? 0);
            $metadata['remaining_seconds_when_paused'] = $remainingSeconds + ($minutes * 60);

            $session->update([
                'metadata' => $metadata,
                'duration_minutes' => $session->duration_minutes + $minutes,
            ]);
        } else {
            $session->update([
                'ends_at' => $session->ends_at ? $session->ends_at->copy()->addMinutes($minutes) : now()->addMinutes($minutes),
                'duration_minutes' => $session->duration_minutes + $minutes,
            ]);
        }

        GameSessionUpdated::dispatch($session->fresh());

        return back()->with('success', $minutes . ' minutes added.');
    }

    public function sendEmails(Request $request, GameSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'emails' => ['required', 'array', 'min:1'],
            'emails.*' => ['required', 'email', 'max:255'],
            'marketing_consent' => ['nullable', 'boolean'],
        ]);

        $session = $session->load(['resource', 'activity', 'teams.players', 'rounds.winningTeam']);
        $marketingConsent = (bool) ($validated['marketing_consent'] ?? false);
        $emails = collect($validated['emails'])->map(fn ($email) => strtolower(trim($email)))->unique()->values();

        foreach ($emails as $email) {
            $customer = Customer::updateOrCreate(
                ['email' => $email],
                [
                    'marketing_consent' => $marketingConsent,
                    'marketing_consent_at' => $marketingConsent ? now() : null,
                    'marketing_source' => $marketingConsent ? 'Playard games tablet' : null,
                    'last_game_at' => now(),
                ]
            );

            $customer->increment('games_played');

            $log = EmailLog::create([
                'customer_id' => $customer->id,
                'game_session_id' => $session->id,
                'email' => $email,
                'type' => 'scorecard',
                'status' => 'pending',
            ]);

            try {
                Mail::to($email)->send(new GameResultsMail($session));

                $log->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $exception) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('play.tablet', $session->resource)
            ->with('success', 'Scorecard links saved and emails processed.');
    }

    public function exportCustomersCsv()
    {
        $fileName = 'playard-games-marketing-customers-' . now()->format('Y-m-d-His') . '.csv';

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'first_name',
                'last_name',
                'email',
                'marketing_consent',
                'marketing_consent_at',
                'marketing_source',
                'last_game_at',
                'games_played',
            ]);

            Customer::query()
                ->where('marketing_consent', true)
                ->orderBy('email')
                ->chunk(200, function ($customers) use ($handle) {
                    foreach ($customers as $customer) {
                        fputcsv($handle, [
                            $customer->first_name,
                            $customer->last_name,
                            $customer->email,
                            $customer->marketing_consent ? 'yes' : 'no',
                            optional($customer->marketing_consent_at)->toDateTimeString(),
                            $customer->marketing_source,
                            optional($customer->last_game_at)->toDateTimeString(),
                            $customer->games_played,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function finishSession(GameSession $session): void
    {
        $session = $session->fresh(['teams']);
        $this->recalculateTotals($session);
        $teams = $session->teams()->orderByDesc('total_score')->get();
        $winner = $teams->first();
        $second = $teams->skip(1)->first();

        $session->update([
            'status' => 'finished',
            'ended_at' => now(),
            'winner_team_name' => $winner && (! $second || $winner->total_score > $second->total_score)
                ? $winner->name
                : 'Draw',
        ]);
    }

    private function recalculateTotals(GameSession $session): void
    {
        $teams = $session->teams()->get();

        foreach ($teams as $team) {
            $total = GameRound::query()
                ->where('game_session_id', $session->id)
                ->where('winning_team_id', $team->id)
                ->sum('points');

            $team->update(['total_score' => $total]);
        }

        $ordered = $session->teams()->orderBy('id')->get();

        $session->update([
            'team_one_total' => $ordered->get(0)?->total_score ?? 0,
            'team_two_total' => $ordered->get(1)?->total_score ?? 0,
        ]);
    }

    private function randomCommentary(int $points): string
    {
        $comments = [
            0 => [
                'No score. The house survived that chaos.',
                'Blank round. Everyone breathe.',
                'Nothing in it. Reset and go again.',
            ],
            1 => [
                'One point stolen. Tidy work.',
                'Small score, big pressure.',
                'One on the board. Ice cold.',
            ],
            2 => [
                'Two points. Now it is getting spicy.',
                'Double trouble on the lane.',
                'That round had main character energy.',
            ],
            3 => [
                'Three points. Absolute scenes.',
                'That was not a round. That was a statement.',
                'Three on the board. Someone check the opposition.',
            ],
        ];

        if ($points >= 4) {
            return collect([
                'Four or more. That is curling violence.',
                'Huge round. The lane just witnessed history.',
                'That score needs a replay.',
            ])->random();
        }

        return collect($comments[$points] ?? $comments[0])->random();
    }

    private function funTeamNames(): array
    {
        return [
            'Stone Cold Legends',
            'Curl Power',
            'Sweep Dreams',
            'The Rock Stars',
            'Ice Breakers',
            'House Hunters',
            'Final Stone Heroes',
            'The Curlfriends',
        ];
    }
}
