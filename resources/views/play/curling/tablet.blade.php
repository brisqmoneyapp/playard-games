

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playard Curling | {{ $resource->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <style>
        .cursor-hidden, .cursor-hidden * {
            cursor: none !important;
        }

        @media (display-mode: fullscreen) {
            .kiosk-only-hint {
                display: none;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-950 text-white kiosk-body">
    <main class="mx-auto max-w-6xl px-5 py-6">
        <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="inline-flex rounded-xl bg-red-600 px-4 py-2 text-3xl font-black tracking-tight">PLAYARD</div>
                <h1 class="mt-4 text-5xl font-black">{{ $resource->name }} Curling</h1>
                <p class="mt-2 text-zinc-400">Enter your teams, start the timer, keep score and share your result.</p>
            </div>
            <div></div>
        </header>


        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if (! $session)
            <section class="rounded-3xl border border-white/10 bg-zinc-900 p-8 text-center">
                <h2 class="text-4xl font-black">No active game</h2>
                <p class="mt-3 text-zinc-400">Ask a team member to launch {{ $resource->name }} from the staff dashboard.</p>
                <p class="mt-6 inline-flex rounded-2xl bg-red-600 px-6 py-4 font-black">
                    Waiting for staff to launch this lane
                </p>
            </section>
        @elseif ($session->status === 'setup')
            <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <form method="POST" action="{{ route('play.setup', $session) }}" class="rounded-3xl border border-white/10 bg-zinc-900 p-6">
                    @csrf
                    <div class="mb-6">
                        <p class="text-sm font-bold uppercase tracking-wider text-red-400">Game lobby</p>
                        <h2 class="text-4xl font-black">Build your teams</h2>
                        <p class="mt-2 text-zinc-400">Add players one by one, then split teams manually or randomise them.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label>
                            <span class="mb-2 block font-bold">Team Red name</span>
                            <input id="teamOneName" name="team_one_name" value="Stone Cold Legends" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-bold text-white" required>
                        </label>
                        <label>
                            <span class="mb-2 block font-bold">Team Yellow name</span>
                            <input id="teamTwoName" name="team_two_name" value="Curl Power" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-bold text-white" required>
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <button type="button" onclick="generateTeamNames()" class="rounded-2xl bg-white px-4 py-4 font-black text-black hover:bg-zinc-200">
                            Generate team names
                        </button>
                        <button type="button" onclick="randomiseTeams()" class="rounded-2xl bg-yellow-400 px-4 py-4 font-black text-black hover:bg-yellow-300">
                            Randomise teams
                        </button>
                        <button type="button" onclick="clearPlayers()" class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4 font-black hover:bg-white/20">
                            Clear players
                        </button>
                    </div>

                    <div class="mt-6 rounded-3xl bg-black/40 p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-end">
                            <label class="flex-1">
                                <span class="mb-2 block font-bold">Add player</span>
                                <input id="playerNameInput" type="text" placeholder="Enter player name" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-bold text-white">
                            </label>
                            <button type="button" onclick="addPlayer()" class="rounded-2xl bg-red-600 px-6 py-4 text-lg font-black hover:bg-red-500">
                                Add to lobby
                            </button>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-3xl border border-red-500/30 bg-red-600/10 p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-2xl font-black">Team Red</h3>
                                    <span id="teamOneCount" class="rounded-full bg-red-600 px-3 py-1 text-sm font-black">0</span>
                                </div>
                                <div id="teamOneCards" class="mt-4 grid gap-3"></div>
                            </div>

                            <div class="rounded-3xl border border-yellow-400/30 bg-yellow-400/10 p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-2xl font-black text-yellow-200">Team Yellow</h3>
                                    <span id="teamTwoCount" class="rounded-full bg-yellow-400 px-3 py-1 text-sm font-black text-black">0</span>
                                </div>
                                <div id="teamTwoCards" class="mt-4 grid gap-3"></div>
                            </div>
                        </div>

                        <div id="hiddenPlayers"></div>
                    </div>

                    <button type="submit" class="mt-6 w-full rounded-3xl bg-red-600 px-6 py-5 text-2xl font-black hover:bg-red-500">
                        Save Teams
                    </button>
                </form>

                @if ($session->teams->count() >= 2)
                    <form method="POST" action="{{ route('play.start', $session) }}" class="rounded-3xl border border-green-500/30 bg-green-500/10 p-6 lg:col-span-2">
                        @csrf

                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wider text-green-300">Teams saved</p>
                                <h3 class="text-3xl font-black">Ready to start?</h3>
                                <p class="mt-2 text-green-100/80">Press Start Game when everyone is ready. The countdown starts immediately.</p>
                            </div>

                            <button type="submit" class="rounded-3xl bg-white px-8 py-5 text-2xl font-black text-black hover:bg-zinc-200">
                                Start Game
                            </button>
                        </div>
                    </form>
                @endif

                <aside class="space-y-6">
                    <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm font-bold uppercase tracking-wider text-red-400">Game length</p>
                        <p class="mt-2 text-5xl font-black">{{ $session->duration_minutes }} mins</p>
                    </section>

                    <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <h3 class="text-2xl font-black">Playard team name generator</h3>
                        <p class="mt-2 text-zinc-400">Tap generate to instantly fill both team names with something better than Team A and Team B.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($funNames as $name)
                                <span class="rounded-full bg-white/10 px-3 py-2 text-sm font-bold">{{ $name }}</span>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <h3 class="text-2xl font-black">How to play</h3>
                        <ol class="mt-4 list-decimal space-y-2 pl-5 text-zinc-300">
                            <li>Split into two teams.</li>
                            <li>Take turns sliding stones towards the target.</li>
                            <li>The closest stone to the centre wins the round.</li>
                            <li>Only one team scores each round.</li>
                            <li>Score one point for each stone closer than the opponent's best stone.</li>
                            <li>Highest score wins. If tied, play sudden death.</li>
                        </ol>
                    </section>
                </aside>
            </section>
        @elseif ($session->status === 'playing')
            @php
                $teams = $session->teams;
                $teamOne = $teams->get(0);
                $teamTwo = $teams->get(1);
                $endsAt = optional($session->ends_at)->toIso8601String();
            @endphp

            <section class="grid gap-6 lg:grid-cols-[1fr_420px]">
                <div class="rounded-3xl border border-white/10 bg-zinc-900 p-6">
                    <div class="text-center">
                        <p class="text-sm font-bold uppercase tracking-wider text-red-400">Time left</p>
                        <div id="countdown" data-ends-at="{{ $endsAt }}" class="mt-2 text-8xl font-black tracking-tight md:text-9xl">
                            --:--
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        @foreach ($teams as $team)
                            <div class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600' : 'bg-yellow-400 text-black' }} p-6 text-center">
                                <p class="text-xl font-black uppercase">{{ $team->name }}</p>
                                <p class="mt-4 text-8xl font-black">{{ $team->total_score }}</p>
                                <div class="mt-4 text-sm font-bold opacity-80">
                                    @foreach ($team->players as $player)
                                        <span>{{ $player->name }}</span>@if (! $loop->last), @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form id="scoreForm" method="POST" action="{{ route('play.score', $session) }}" class="mt-6 rounded-3xl bg-black/40 p-5">
                        @csrf

                        <input id="winningTeamInput" type="hidden" name="winning_team_id">
                        <input id="pointsInput" type="hidden" name="points" value="0">

                        <h3 class="text-2xl font-black">Add round score</h3>
                        <p class="mt-1 text-zinc-400">Tap winner, tap points, save instantly.</p>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @foreach ($teams as $team)
                                <button
                                    type="button"
                                    data-team-button
                                    data-team-id="{{ $team->id }}"
                                    onclick="selectWinningTeam('{{ $team->id }}')"
                                    class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600 text-white' : 'bg-yellow-400 text-black' }} px-5 py-6 text-2xl font-black opacity-80 transition hover:opacity-100"
                                >
                                    {{ $team->name }} won
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-4 grid grid-cols-5 gap-3">
                            @foreach ([0,1,2,3,4] as $point)
                                <button
                                    type="button"
                                    data-point-button
                                    data-point="{{ $point }}"
                                    onclick="selectPoints('{{ $point }}')"
                                    class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4 text-2xl font-black hover:bg-white/20"
                                >
                                    {{ $point }}
                                </button>
                            @endforeach
                        </div>

                        <button class="mt-4 w-full rounded-2xl bg-red-600 px-5 py-4 text-xl font-black hover:bg-red-500">
                            Save Round
                        </button>
                    </form>

                    <form id="autoEndForm" method="POST" action="{{ route('staff.sessions.end', $session) }}" class="hidden">
                        @csrf
                    </form>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <h3 class="text-2xl font-black">Rounds</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($session->rounds->sortByDesc('round_number') as $round)
                                <div class="rounded-2xl bg-black/40 p-4">
                                    <p class="font-black">Round {{ $round->round_number }}</p>
                                    <p class="text-zinc-300">{{ $round->winningTeam?->name ?? 'No score' }} scored {{ $round->points }}</p>
                                    <p class="mt-1 text-sm text-zinc-400">{{ $round->commentary }}</p>
                                </div>
                            @empty
                                <p class="text-zinc-400">No rounds scored yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <form method="POST" action="{{ route('staff.sessions.end', $session) }}">
                        @csrf
                        <button class="w-full rounded-3xl bg-white px-6 py-5 text-2xl font-black text-black hover:bg-zinc-200">
                            End Game
                        </button>
                    </form>
                </aside>
            </section>
        @elseif ($session->status === 'finished')
            @php
                $teams = $session->teams->sortByDesc('total_score')->values();
                $winner = $teams->first();
            @endphp

            <section class="grid gap-6 lg:grid-cols-[1fr_420px]">
                <div class="rounded-3xl border border-white/10 bg-zinc-900 p-8 text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-red-400">Final result</p>
                    <h2 class="mt-3 text-6xl font-black">{{ $session->winner_team_name }}</h2>
                    <p class="mt-3 text-2xl text-zinc-300">{{ $session->winner_team_name === 'Draw' ? 'It ended level. Sudden death next time.' : 'Stone cold winners.' }}</p>
                    <p class="mt-3 inline-flex rounded-full bg-red-600 px-5 py-2 text-sm font-black uppercase tracking-wider">
                        Bragging rights activated
                    </p>

                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        @foreach ($session->teams as $team)
                            <div class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600' : 'bg-yellow-400 text-black' }} p-6">
                                <p class="text-xl font-black">{{ $team->name }}</p>
                                <p class="mt-3 text-8xl font-black">{{ $team->total_score }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-col gap-3 md:flex-row md:justify-center">
                        <a href="{{ route('share.show', $session->share_code) }}" class="rounded-2xl bg-white px-6 py-4 font-black text-black hover:bg-zinc-200">
                            Open Share Page
                        </a>
                    </div>
                </div>

                <aside class="rounded-3xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-2xl font-black">Send scorecard</h3>
                    <p class="mt-2 text-zinc-400">Add multiple emails. Marketing export only includes people who tick the consent box.</p>

                    <form method="POST" action="{{ route('play.emails', $session) }}" class="mt-5 space-y-3">
                        @csrf
                        @for ($i = 0; $i < 5; $i++)
                            <input name="emails[]" type="email" placeholder="Email {{ $i + 1 }}" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 font-bold text-white" @if($i === 0) required @endif>
                        @endfor

                        <label class="flex gap-3 rounded-2xl bg-black/40 p-4 text-left">
                            <input type="checkbox" name="marketing_consent" value="1" class="mt-1 h-5 w-5">
                            <span class="text-sm text-zinc-300">I agree to receive Playard offers, events and discounts by email.</span>
                        </label>

                        <button class="w-full rounded-2xl bg-red-600 px-5 py-4 text-xl font-black hover:bg-red-500">
                            Send Results
                        </button>
                    </form>
                </aside>
            </section>
        @else
            <section class="rounded-3xl border border-white/10 bg-zinc-900 p-8 text-center">
                <h2 class="text-4xl font-black">This game is {{ $session->status }}</h2>
                <p class="mt-6 inline-flex rounded-2xl bg-red-600 px-6 py-4 font-black">Please ask staff to reset this lane.</p>
            </section>
        @endif
    </main>

    <script>
        let wakeLock = null;
        let cursorHideTimer = null;

        async function enterFullscreenMode() {
            const element = document.documentElement;

            if (element.requestFullscreen) {
                await element.requestFullscreen();
            } else if (element.webkitRequestFullscreen) {
                await element.webkitRequestFullscreen();
            }

            await requestWakeLock();
        }

        async function requestWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                }
            } catch (error) {
                console.warn('Wake lock unavailable', error);
            }
        }


        function resetCursorTimer() {
            document.body.classList.remove('cursor-hidden');
            clearTimeout(cursorHideTimer);

            cursorHideTimer = setTimeout(() => {
                document.body.classList.add('cursor-hidden');
            }, 2500);
        }

        function protectKioskNavigation() {
            history.pushState(null, '', location.href);
            window.addEventListener('popstate', () => {
                history.pushState(null, '', location.href);
            });
        }

        function showConnectionStatus() {
            if (!navigator.onLine) {
                alert('This tablet appears to be offline. Scores may not save until the connection returns.');
            }
        }

        window.addEventListener('online', () => {
            console.log('Tablet is online');
        });

        window.addEventListener('offline', showConnectionStatus);

        document.addEventListener('visibilitychange', async () => {
            if (document.visibilityState === 'visible') {
                await requestWakeLock();
            }
        });

        document.addEventListener('mousemove', resetCursorTimer);
        document.addEventListener('touchstart', resetCursorTimer);

        protectKioskNavigation();
        resetCursorTimer();

        let lobbyPlayers = [];

        const premiumTeamNames = [
            'Stone Cold Legends',
            'Curl Power',
            'Sweep Dreams',
            'The Rock Stars',
            'House Hunters',
            'Final Stone Heroes',
            'The Almost Professionals',
            'The Lucky Sliders',
            'Questionable Tactics',
            'The Calm Before The Score',
            'Zero Practice Required',
            'The Lane Stealers'
        ];

        function addPlayer() {
            const input = document.getElementById('playerNameInput');
            if (!input) return;

            const name = input.value.trim();
            if (!name) return;

            const teamOneCount = lobbyPlayers.filter(player => player.team === 'one').length;
            const teamTwoCount = lobbyPlayers.filter(player => player.team === 'two').length;

            lobbyPlayers.push({
                name,
                team: teamOneCount <= teamTwoCount ? 'one' : 'two'
            });

            input.value = '';
            renderLobbyPlayers();
        }

        function removePlayer(index) {
            lobbyPlayers.splice(index, 1);
            renderLobbyPlayers();
        }

        function movePlayer(index, team) {
            lobbyPlayers[index].team = team;
            renderLobbyPlayers();
        }

        function randomiseTeams() {
            lobbyPlayers = lobbyPlayers
                .sort(() => Math.random() - 0.5)
                .map((player, index) => ({
                    ...player,
                    team: index % 2 === 0 ? 'one' : 'two'
                }));

            renderLobbyPlayers();
        }

        function clearPlayers() {
            lobbyPlayers = [];
            renderLobbyPlayers();
        }

        function generateTeamNames() {
            const shuffled = [...premiumTeamNames].sort(() => Math.random() - 0.5);
            const teamOne = document.getElementById('teamOneName');
            const teamTwo = document.getElementById('teamTwoName');

            if (teamOne) teamOne.value = shuffled[0];
            if (teamTwo) teamTwo.value = shuffled[1] ?? 'Curl Power';
        }

        function renderLobbyPlayers() {
            const teamOneCards = document.getElementById('teamOneCards');
            const teamTwoCards = document.getElementById('teamTwoCards');
            const hiddenPlayers = document.getElementById('hiddenPlayers');
            const teamOneCount = document.getElementById('teamOneCount');
            const teamTwoCount = document.getElementById('teamTwoCount');

            if (!teamOneCards || !teamTwoCards || !hiddenPlayers) return;

            teamOneCards.innerHTML = '';
            teamTwoCards.innerHTML = '';
            hiddenPlayers.innerHTML = '';

            lobbyPlayers.forEach((player, index) => {
                const card = document.createElement('div');
                card.className = 'rounded-2xl border border-white/10 bg-zinc-950 p-4 shadow-xl';
                card.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-lg font-black">${escapeHtml(player.name)}</p>
                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Ready to slide</p>
                        </div>
                        <button type="button" onclick="removePlayer(${index})" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-black hover:bg-white/20">Remove</button>
                    </div>
                    <button type="button" onclick="movePlayer(${index}, '${player.team === 'one' ? 'two' : 'one'}')" class="mt-3 w-full rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-black hover:bg-white/20">
                        Move to ${player.team === 'one' ? 'Yellow' : 'Red'}
                    </button>
                `;

                if (player.team === 'one') {
                    teamOneCards.appendChild(card);
                } else {
                    teamTwoCards.appendChild(card);
                }

                hiddenPlayers.insertAdjacentHTML('beforeend', `
                    <input type="hidden" name="players[${index}][name]" value="${escapeHtml(player.name)}">
                    <input type="hidden" name="players[${index}][team]" value="${player.team}">
                `);
            });

            const oneCount = lobbyPlayers.filter(player => player.team === 'one').length;
            const twoCount = lobbyPlayers.filter(player => player.team === 'two').length;

            if (teamOneCount) teamOneCount.textContent = oneCount;
            if (teamTwoCount) teamTwoCount.textContent = twoCount;

            if (oneCount === 0) {
                teamOneCards.innerHTML = '<p class="rounded-2xl bg-black/30 p-4 text-zinc-500">No players yet.</p>';
            }

            if (twoCount === 0) {
                teamTwoCards.innerHTML = '<p class="rounded-2xl bg-black/30 p-4 text-zinc-500">No players yet.</p>';
            }
        }

        function escapeHtml(value) {
            return value
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('playerNameInput');
            if (input) {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        addPlayer();
                    }
                });
            }

            enterFullscreenMode();
            renderLobbyPlayers();
            selectPoints('0');

            @if ($session && $session->status === 'finished')
                launchWinnerConfetti();
            @endif
        });

        let autoEndTriggered = false;

        function selectWinningTeam(teamId) {
            const input = document.getElementById('winningTeamInput');
            if (input) input.value = teamId;

            document.querySelectorAll('[data-team-button]').forEach(button => {
                button.classList.remove('ring-4', 'ring-white', 'scale-105');
                button.classList.add('opacity-80');
            });

            const selected = document.querySelector(`[data-team-button][data-team-id="${teamId}"]`);

            if (selected) {
                selected.classList.add('ring-4', 'ring-white', 'scale-105');
                selected.classList.remove('opacity-80');
            }
        }

        function selectPoints(points) {
            const input = document.getElementById('pointsInput');
            if (input) input.value = points;

            document.querySelectorAll('[data-point-button]').forEach(button => {
                button.classList.remove('bg-white', 'text-black');
                button.classList.add('bg-white/10');
            });

            const selected = document.querySelector(`[data-point-button][data-point="${points}"]`);

            if (selected) {
                selected.classList.add('bg-white', 'text-black');
                selected.classList.remove('bg-white/10');
            }
        }

        function launchWinnerConfetti() {
            if (typeof confetti === 'undefined') return;

            const duration = 2500;
            const end = Date.now() + duration;

            (function frame() {
                confetti({
                    particleCount: 4,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0 },
                });

                confetti({
                    particleCount: 4,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1 },
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            })();
        }

        function updateCountdown() {
            const el = document.getElementById('countdown');
            if (!el) return;

            const endsAt = new Date(el.dataset.endsAt).getTime();
            const now = new Date().getTime();
            const diff = Math.max(0, Math.floor((endsAt - now) / 1000));
            const minutes = Math.floor(diff / 60).toString().padStart(2, '0');
            const seconds = (diff % 60).toString().padStart(2, '0');

            el.textContent = `${minutes}:${seconds}`;

            if (diff === 0 && !autoEndTriggered) {
                autoEndTriggered = true;
                el.textContent = 'TIME UP';

                const autoEndForm = document.getElementById('autoEndForm');

                if (autoEndForm) {
                    setTimeout(() => autoEndForm.submit(), 1500);
                }
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
        requestWakeLock();
    </script>
</body>
</html>