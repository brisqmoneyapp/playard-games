

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playard Curling | {{ $resource->name }}</title>
    <meta name="lane-state" content="{{ $session ? $session->status . '-' . optional($session->updated_at)->timestamp : 'none' }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    @vite(['resources/js/app.js'])
    <style>
        html,
        body {
            width: 100%;
            min-height: 100%;
            height: auto;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior-y: auto;
            touch-action: pan-y;
            -webkit-overflow-scrolling: touch;
        }

        .android-scroll-safe {
            max-height: none !important;
            overflow: visible !important;
        }

        .cursor-hidden, .cursor-hidden * {
            cursor: none !important;
        }

        .thin-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .thin-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .thin-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 999px;
        }

        .pulse-danger {
            animation: pulse-danger 1s ease-in-out infinite;
        }

        @keyframes pulse-danger {
            0%, 100% {
                transform: scale(1);
                text-shadow: 0 0 0 rgba(239, 68, 68, 0);
            }
            50% {
                transform: scale(1.06);
                text-shadow: 0 0 30px rgba(239, 68, 68, 0.85);
            }
        }

        .avatar-chip {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(250, 204, 21, 0.95));
        }

        .screen-saver-visible {
            opacity: 1 !important;
            pointer-events: auto !important;
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-950 text-white kiosk-body">
    <main class="android-scroll-safe min-h-screen overflow-x-hidden overflow-y-auto px-5 py-4">
        <header class="mb-4 flex shrink-0 flex-row items-center justify-between gap-4">
            <div>
                <div class="inline-flex rounded-xl bg-red-600 px-4 py-2 text-2xl font-black tracking-tight">PLAYARD</div>
                <h1 class="mt-2 text-4xl font-black leading-none">{{ $resource->name }} Curling</h1>
                <p class="mt-1 text-sm text-zinc-400">Enter teams, play, score, share.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="openHowToPlayModal()" class="rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-center font-black hover:bg-white/20">
                    How to Play
                </button>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-right">
                    <p class="text-xs font-black uppercase tracking-wider text-red-400">Lane screen</p>
                    <p class="text-lg font-black">Customer mode</p>
                    <p id="connectionIndicator" class="mt-1 text-xs font-black text-yellow-400">● Connecting</p>
                </div>
            </div>
        </header>

        <div id="screenSaver" class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/95 opacity-0 transition-opacity duration-500">
            <div class="text-center">
                <div class="mx-auto inline-flex rounded-2xl bg-red-600 px-6 py-3 text-4xl font-black">PLAYARD</div>
                <h2 class="mt-6 text-6xl font-black">Tap to continue</h2>
                <p class="mt-3 text-xl text-zinc-400">Curling lane is still ready.</p>
            </div>
        </div>

        <div id="howToPlayModal" class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center bg-black/85 px-6 opacity-0 transition-opacity duration-300">
            <div class="thin-scrollbar max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/10 bg-zinc-950 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-wider text-red-400">Quick guide</p>
                        <h2 class="mt-2 text-5xl font-black leading-none">How to Play Curling</h2>
                        <p class="mt-2 text-zinc-400">Simple Playard rules. Read this, close it, then get sliding.</p>
                    </div>
                    <button type="button" onclick="closeHowToPlayModal()" class="rounded-2xl bg-white px-5 py-3 font-black text-black hover:bg-zinc-200">
                        Close
                    </button>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_1.1fr]">
                    <section class="rounded-3xl bg-white/5 p-5">
                        <h3 class="text-2xl font-black">Basic rules</h3>
                        <ol class="mt-4 space-y-3 text-lg text-zinc-200">
                            <li><span class="font-black text-white">1.</span> Split into two teams: Red and Yellow.</li>
                            <li><span class="font-black text-white">2.</span> Teams take turns sliding stones towards the target.</li>
                            <li><span class="font-black text-white">3.</span> You can knock other stones out of the way during play.</li>
                            <li><span class="font-black text-white">4.</span> After all stones are played, check which stone is closest to the centre.</li>
                            <li><span class="font-black text-white">5.</span> Only the team with the closest stone scores in that round.</li>
                            <li><span class="font-black text-white">6.</span> Count how many of that team’s stones are closer than the opponent’s best stone.</li>
                            <li><span class="font-black text-white">7.</span> The team with the highest total score wins the game.</li>
                            <li><span class="font-black text-white">8.</span> If the score is level, play one final tiebreak round.</li>
                        </ol>

                        <div class="mt-5 rounded-3xl border border-red-500/30 bg-red-600/10 p-5">
                            <h4 class="text-xl font-black">Simple scoring phrase</h4>
                            <p class="mt-2 text-zinc-300">Closest stone wins the round. Extra stones closer than the opponent’s best stone score extra points.</p>

                            <div class="mt-4 rounded-2xl bg-black/30 p-4">
                                <p class="text-sm font-black uppercase tracking-wider text-yellow-300">Quick example</p>
                                <p class="mt-2 text-sm text-zinc-300">
                                    Red has the closest stone.<br>
                                    Red also has another stone closer than Yellow’s nearest stone.<br><br>
                                    <span class="font-black text-white">Result: Red scores 2 points.</span>
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-white p-5 text-black">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-black uppercase tracking-wider text-red-600">Scoring example</p>
                                <h3 class="text-3xl font-black">Red scores 2</h3>
                                <p class="mt-2 text-sm font-bold text-zinc-600">Red has two stones closer to the centre than Yellow’s best stone.</p>
                            </div>
                            <div class="rounded-2xl bg-red-600 px-4 py-2 text-sm font-black text-white">RED +2</div>
                        </div>

                        <div class="relative mx-auto mt-6 aspect-square max-w-[420px] rounded-full border-[18px] border-blue-600 bg-white shadow-2xl">
                            <div class="absolute inset-[14%] rounded-full border-[18px] border-white bg-red-600"></div>
                            <div class="absolute inset-[31%] rounded-full border-[14px] border-white bg-blue-600"></div>
                            <div class="absolute inset-[44%] rounded-full bg-white"></div>
                            <div class="absolute left-1/2 top-1/2 h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full bg-black"></div>

                            <div class="absolute left-[47%] top-[36%] flex h-10 w-10 items-center justify-center rounded-full bg-red-600 text-lg font-black text-white ring-4 ring-white">R</div>
                            <div class="absolute left-[56%] top-[42%] flex h-10 w-10 items-center justify-center rounded-full bg-red-600 text-lg font-black text-white ring-4 ring-white">R</div>
                            <div class="absolute left-[33%] top-[53%] flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400 text-lg font-black text-black ring-4 ring-white">Y</div>
                            <div class="absolute left-[66%] top-[63%] flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400 text-lg font-black text-black ring-4 ring-white">Y</div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-3">
                            <div class="rounded-2xl bg-zinc-100 p-4">
                                <p class="font-black">Closest stone?</p>
                                <p class="mt-1 text-sm text-zinc-600">Red is closest.</p>
                            </div>
                            <div class="rounded-2xl bg-zinc-100 p-4">
                                <p class="font-black">How many count?</p>
                                <p class="mt-1 text-sm text-zinc-600">Two red stones beat Yellow’s best.</p>
                            </div>
                            <div class="rounded-2xl bg-zinc-100 p-4">
                                <p class="font-black">Score</p>
                                <p class="mt-1 text-sm text-zinc-600">Red scores 2 points.</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl bg-white p-5 text-black lg:col-span-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-black uppercase tracking-wider text-yellow-600">Another scoring example</p>
                                <h3 class="text-3xl font-black">Yellow scores 1</h3>
                                <p class="mt-2 text-sm font-bold text-zinc-600">Yellow has the closest stone, but Red’s nearest stone is second closest, so only one Yellow stone counts.</p>
                            </div>
                            <div class="rounded-2xl bg-yellow-400 px-4 py-2 text-sm font-black text-black">YELLOW +1</div>
                        </div>

                        <div class="relative mx-auto mt-6 aspect-square max-w-[380px] rounded-full border-[18px] border-blue-600 bg-white shadow-2xl">
                            <div class="absolute inset-[14%] rounded-full border-[18px] border-white bg-red-600"></div>
                            <div class="absolute inset-[31%] rounded-full border-[14px] border-white bg-blue-600"></div>
                            <div class="absolute inset-[44%] rounded-full bg-white"></div>
                            <div class="absolute left-1/2 top-1/2 h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full bg-black"></div>

                            <div class="absolute left-[49%] top-[40%] flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400 text-lg font-black text-black ring-4 ring-white">Y</div>
                            <div class="absolute left-[40%] top-[47%] flex h-10 w-10 items-center justify-center rounded-full bg-red-600 text-lg font-black text-white ring-4 ring-white">R</div>
                            <div class="absolute left-[61%] top-[53%] flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400 text-lg font-black text-black ring-4 ring-white">Y</div>
                            <div class="absolute left-[32%] top-[64%] flex h-10 w-10 items-center justify-center rounded-full bg-red-600 text-lg font-black text-white ring-4 ring-white">R</div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-3">
                            <div class="rounded-2xl bg-zinc-100 p-4">
                                <p class="font-black">Closest stone?</p>
                                <p class="mt-1 text-sm text-zinc-600">Yellow is closest.</p>
                            </div>
                            <div class="rounded-2xl bg-zinc-100 p-4">
                                <p class="font-black">How many count?</p>
                                <p class="mt-1 text-sm text-zinc-600">Only one Yellow stone beats Red’s best.</p>
                            </div>
                            <div class="rounded-2xl bg-zinc-100 p-4">
                                <p class="font-black">Score</p>
                                <p class="mt-1 text-sm text-zinc-600">Yellow scores 1 point.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div id="playerSetupModal" class="pointer-events-none fixed inset-0 z-[75] flex items-center justify-center bg-black/85 px-6 opacity-0 transition-opacity duration-300">
            <div class="w-full max-w-3xl rounded-[2rem] border border-white/10 bg-zinc-950 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-wider text-red-400">Player setup</p>
                        <h2 class="mt-2 text-5xl font-black leading-none">Add players</h2>
                        <p class="mt-2 text-zinc-400">Add one player at a time. Choose Auto, Red or Yellow.</p>
                    </div>
                    <button type="button" onclick="closePlayerSetupModal()" class="rounded-2xl bg-white px-5 py-3 font-black text-black hover:bg-zinc-200">
                        Done
                    </button>
                </div>

                <div class="mt-6 rounded-3xl border border-red-500/20 bg-gradient-to-br from-red-600/20 to-black p-5 text-center">
                    <div id="lastPlayerAdded" class="mb-5 hidden rounded-3xl border border-green-500/30 bg-green-500/10 px-6 py-4 text-green-200">
                        <p class="text-xs font-black uppercase tracking-wider">Player locked in</p>
                        <p id="lastPlayerAddedName" class="mt-1 text-3xl font-black"></p>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-lg font-black">Player name</span>
                        <input id="playerNameInput" type="text" inputmode="text" autocomplete="off" autocapitalize="words" spellcheck="false" placeholder="Type name here" class="w-full rounded-3xl border border-white/10 bg-black px-6 py-5 text-center text-3xl font-black text-white placeholder:text-zinc-600">
                    </label>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <button id="addTeamAutoButton" type="button" onclick="selectAddTeam('auto')" class="rounded-2xl bg-white px-3 py-3 font-black text-black">
                            Auto
                        </button>
                        <button id="addTeamOneButton" type="button" onclick="selectAddTeam('one')" class="rounded-2xl bg-white/10 px-3 py-3 font-black hover:bg-white/20">
                            Red
                        </button>
                        <button id="addTeamTwoButton" type="button" onclick="selectAddTeam('two')" class="rounded-2xl bg-white/10 px-3 py-3 font-black hover:bg-white/20">
                            Yellow
                        </button>
                    </div>

                    <button id="realAddPlayerButton" type="button" onclick="addPlayer()" class="mt-4 w-full rounded-3xl bg-red-600 px-6 py-5 text-2xl font-black active:scale-[0.98] hover:bg-red-500">
                        Add Player
                    </button>

                    <p id="nextPlayerPrompt" class="mt-4 text-lg font-bold text-zinc-400">Add the first player to begin.</p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <button type="button" onclick="randomiseTeams()" class="rounded-2xl bg-yellow-400 px-5 py-4 font-black text-black hover:bg-yellow-300">
                        Shuffle Teams
                    </button>
                    <button type="button" onclick="clearPlayers()" class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 font-black hover:bg-white/20">
                        Reset Lobby
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 shrink-0 rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-3 text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 shrink-0 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if (! $session)
            <section class="flex min-h-0 flex-1 items-center justify-center rounded-3xl border border-white/10 bg-zinc-900 p-8 text-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-red-400">{{ $resource->name }}</p>
                    <h2 class="mt-3 text-5xl font-black">Lane warming up...</h2>
                    <p class="mt-3 text-zinc-400">Grab your team. Your game is about to begin.</p>
                    <p class="mt-6 inline-flex rounded-2xl bg-red-600 px-6 py-4 font-black">
                        Ready when you are
                    </p>
                    <button type="button" onclick="openHowToPlayModal()" class="mt-4 inline-flex rounded-2xl border border-white/10 bg-white/10 px-6 py-4 font-black hover:bg-white/20">
                        Learn How to Play
                    </button>
                </div>
            </section>
        @elseif ($session->status === 'setup')
            <section class="flex min-h-0 flex-1 items-center justify-center overflow-visible">
                <form method="POST" action="{{ route('play.setup', $session) }}" class="flex w-full max-w-6xl flex-col overflow-visible rounded-[2rem] border border-white/10 bg-zinc-900 p-5 shadow-2xl">
                    @csrf

                    <div class="flex shrink-0 items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-red-400">{{ $resource->name }}</p>
                            <h2 class="mt-2 text-5xl font-black leading-none">Set up your teams</h2>
                            <p class="mt-2 text-zinc-400">Add your players, save your teams, then get ready to play.</p>
                        </div>

                        <div class="rounded-2xl bg-white/10 px-5 py-4 text-right">
                            <p class="text-xs font-black uppercase tracking-wider text-zinc-400">Players added</p>
                            <p id="totalPlayerCount" class="text-4xl font-black">0</p>
                        </div>
                    </div>

                    <div class="mt-5 grid shrink-0 gap-3 md:grid-cols-2">
                        <label>
                            <span class="mb-1 block text-sm font-bold">Team Red name</span>
                            <input id="teamOneName" name="team_one_name" value="Stone Cold Legends" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-black text-white" required>
                        </label>
                        <label>
                            <span class="mb-1 block text-sm font-bold">Team Yellow name</span>
                            <input id="teamTwoName" name="team_two_name" value="Curl Power" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-black text-white" required>
                        </label>
                    </div>

                    <div class="android-scroll-safe mt-5 grid gap-4 md:grid-cols-2">
                        <div class="android-scroll-safe flex flex-col rounded-3xl border border-red-500/30 bg-red-600/10 p-5">
                            <div class="flex shrink-0 items-center justify-between">
                                <h3 class="text-3xl font-black">Team Red</h3>
                                <span id="teamOneCount" class="rounded-full bg-red-600 px-4 py-2 text-sm font-black">0</span>
                            </div>
                            <div id="teamOneCards" class="thin-scrollbar mt-4 flex min-h-0 flex-1 flex-wrap content-start gap-2 overflow-y-auto pr-1"></div>
                        </div>

                        <div class="android-scroll-safe flex flex-col rounded-3xl border border-yellow-400/30 bg-yellow-400/10 p-5">
                            <div class="flex shrink-0 items-center justify-between">
                                <h3 class="text-3xl font-black text-yellow-200">Team Yellow</h3>
                                <span id="teamTwoCount" class="rounded-full bg-yellow-400 px-4 py-2 text-sm font-black text-black">0</span>
                            </div>
                            <div id="teamTwoCards" class="thin-scrollbar mt-4 flex min-h-0 flex-1 flex-wrap content-start gap-2 overflow-y-auto pr-1"></div>
                        </div>
                    </div>

                    <div id="hiddenPlayers"></div>

                    <div class="mt-5 grid shrink-0 gap-3 md:grid-cols-5">
                        <button type="button" onclick="openPlayerSetupModal()" class="rounded-3xl bg-red-600 px-5 py-5 text-xl font-black hover:bg-red-500 md:col-span-2">
                            Add Players
                        </button>
                        <button type="button" onclick="generateTeamNames()" class="rounded-3xl bg-white px-5 py-5 text-xl font-black text-black hover:bg-zinc-200">
                            Surprise Names
                        </button>
                        <button type="button" onclick="randomiseTeams()" class="rounded-3xl bg-yellow-400 px-5 py-5 text-xl font-black text-black hover:bg-yellow-300">
                            Shuffle
                        </button>
                        <button type="submit" class="rounded-3xl bg-green-500 px-5 py-5 text-xl font-black text-black hover:bg-green-400">
                            Save Teams
                        </button>
                    </div>
                </form>
            </section>
        @elseif (in_array($session->status, ['playing', 'paused'], true))
            @php
                $teams = $session->teams;
                $endsAt = optional($session->ends_at)->toIso8601String();
                $teamsAreReady = $teams->count() >= 2;
                $isPaused = $session->status === 'paused';
                $pausedRemaining = (int) data_get($session->metadata, 'remaining_seconds_when_paused', 0);
            @endphp

            <section class="grid min-h-0 flex-1 gap-4 overflow-visible lg:grid-cols-[1fr_380px]">
                <div class="android-scroll-safe rounded-3xl border border-white/10 bg-zinc-900 p-5">
                    <div class="text-center">
                        <p class="text-xs font-bold uppercase tracking-wider text-red-400">Time left</p>
                        <div id="countdown" data-ends-at="{{ $endsAt }}" data-status="{{ $session->status }}" data-paused-remaining="{{ $pausedRemaining }}" class="mt-1 text-7xl font-black tracking-tight md:text-8xl">
                            --:--
                        </div>
                        <p id="countdownMessage" class="mt-1 text-sm font-black uppercase tracking-wider text-zinc-500">
                            {{ $isPaused ? 'Paused by staff.' : ($teamsAreReady ? 'Game on' : 'Add your players.') }}
                        </p>
                    </div>

                    @if (! $teamsAreReady)
                        <form method="POST" action="{{ route('play.setup', $session) }}" class="android-scroll-safe mt-5 flex flex-col rounded-3xl border border-white/10 bg-black/40 p-5">
                            @csrf

                            <div class="flex shrink-0 items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wider text-yellow-300">Timer is running</p>
                                    <h2 class="mt-2 text-4xl font-black leading-none">Add your players</h2>
                                    <p class="mt-2 text-zinc-400">Add your players, save teams, then start scoring.</p>
                                </div>

                                <div class="rounded-2xl bg-white/10 px-5 py-4 text-right">
                                    <p class="text-xs font-black uppercase tracking-wider text-zinc-400">Players added</p>
                                    <p id="totalPlayerCount" class="text-4xl font-black">0</p>
                                </div>
                            </div>

                            <div class="mt-5 grid shrink-0 gap-3 md:grid-cols-2">
                                <label>
                                    <span class="mb-1 block text-sm font-bold">Team Red name</span>
                                    <input id="teamOneName" name="team_one_name" value="Stone Cold Legends" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-black text-white" required>
                                </label>
                                <label>
                                    <span class="mb-1 block text-sm font-bold">Team Yellow name</span>
                                    <input id="teamTwoName" name="team_two_name" value="Curl Power" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-4 text-lg font-black text-white" required>
                                </label>
                            </div>

                            <div class="android-scroll-safe mt-5 grid gap-4 md:grid-cols-2">
                                <div class="android-scroll-safe flex flex-col rounded-3xl border border-red-500/30 bg-red-600/10 p-4">
                                    <div class="flex shrink-0 items-center justify-between">
                                        <h3 class="text-2xl font-black">Team Red</h3>
                                        <span id="teamOneCount" class="rounded-full bg-red-600 px-3 py-1 text-sm font-black">0</span>
                                    </div>
                                    <div id="teamOneCards" class="thin-scrollbar mt-3 flex min-h-0 flex-1 flex-wrap content-start gap-2 overflow-y-auto pr-1"></div>
                                </div>

                                <div class="android-scroll-safe flex flex-col rounded-3xl border border-yellow-400/30 bg-yellow-400/10 p-4">
                                    <div class="flex shrink-0 items-center justify-between">
                                        <h3 class="text-2xl font-black text-yellow-200">Team Yellow</h3>
                                        <span id="teamTwoCount" class="rounded-full bg-yellow-400 px-3 py-1 text-sm font-black text-black">0</span>
                                    </div>
                                    <div id="teamTwoCards" class="thin-scrollbar mt-3 flex min-h-0 flex-1 flex-wrap content-start gap-2 overflow-y-auto pr-1"></div>
                                </div>
                            </div>

                            <div id="hiddenPlayers"></div>

                            <div class="mt-5 grid shrink-0 gap-3 md:grid-cols-4">
                                <button type="button" onclick="openPlayerSetupModal()" class="rounded-3xl bg-red-600 px-5 py-5 text-xl font-black hover:bg-red-500 md:col-span-2">
                                    Add Players
                                </button>
                                <button type="button" onclick="randomiseTeams()" class="rounded-3xl bg-yellow-400 px-5 py-5 text-xl font-black text-black hover:bg-yellow-300">
                                    Shuffle
                                </button>
                                <button type="submit" class="rounded-3xl bg-green-500 px-5 py-5 text-xl font-black text-black hover:bg-green-400">
                                    Save Teams
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ($teams as $team)
                                <div class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600' : 'bg-yellow-400 text-black' }} p-5 text-center">
                                    <p class="text-xl font-black uppercase">{{ $team->name }}</p>
                                    <p class="mt-2 text-7xl font-black">{{ $team->total_score }}</p>
                                    <div class="mt-3 flex justify-center gap-2 overflow-hidden">
                                        @foreach ($team->players->take(5) as $player)
                                            <div class="avatar-chip flex h-9 w-9 items-center justify-center rounded-full text-sm font-black text-black" title="{{ $player->name }}">
                                                {{ strtoupper(substr($player->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 truncate text-xs font-bold opacity-80">
                                        @foreach ($team->players as $player)
                                            <span>{{ $player->name }}</span>@if (! $loop->last), @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form id="scoreForm" method="POST" action="{{ route('play.score', $session) }}" class="mt-5 rounded-3xl bg-black/40 p-4">
                            @csrf

                            <input id="winningTeamInput" type="hidden" name="winning_team_id">
                            <input id="pointsInput" type="hidden" name="points" value="0">

                            <div class="flex items-end justify-between gap-4">
                                <div>
                                    <h3 class="text-2xl font-black">Add score</h3>
                                    <p class="mt-1 text-sm text-zinc-400">Tap winner, tap points, save.</p>
                                </div>
                                <p class="rounded-full bg-white/10 px-4 py-2 text-sm font-black text-zinc-300">Round {{ $session->rounds->count() + 1 }}</p>
                            </div>

                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                @foreach ($teams as $team)
                                    <button
                                        type="button"
                                        data-team-button
                                        data-team-id="{{ $team->id }}"
                                        onclick="selectWinningTeam('{{ $team->id }}')"
                                        class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600 text-white' : 'bg-yellow-400 text-black' }} px-5 py-5 text-xl font-black opacity-80 transition hover:opacity-100"
                                    >
                                        {{ $team->name }} won
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-3 grid grid-cols-5 gap-2">
                                @foreach ([0,1,2,3,4] as $point)
                                    <button
                                        type="button"
                                        data-point-button
                                        data-point="{{ $point }}"
                                        onclick="selectPoints('{{ $point }}')"
                                        class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-2xl font-black hover:bg-white/20"
                                    >
                                        {{ $point }}
                                    </button>
                                @endforeach
                            </div>

                            <button class="mt-3 w-full rounded-2xl bg-red-600 px-5 py-3 text-xl font-black hover:bg-red-500">
                                Save Round
                            </button>
                        </form>
                    @endif

                    <form id="autoEndForm" method="POST" action="{{ route('staff.sessions.end', $session) }}" class="hidden">
                        @csrf
                    </form>
                </div>

                <aside class="android-scroll-safe space-y-4">
                    @if ($teamsAreReady)
                        <section class="android-scroll-safe rounded-3xl border border-white/10 bg-white/5 p-5">
                            <h3 class="text-2xl font-black">Rounds</h3>
                            <div class="thin-scrollbar android-scroll-safe mt-3 space-y-2 pr-1">
                                @forelse ($session->rounds->sortByDesc('round_number') as $round)
                                    <div class="rounded-2xl bg-black/40 p-3">
                                        <p class="font-black">Round {{ $round->round_number }}</p>
                                        <p class="text-sm text-zinc-300">{{ $round->winningTeam?->name ?? 'No score' }} scored {{ $round->points }}</p>
                                        <p class="mt-1 text-xs text-zinc-400">{{ $round->commentary }}</p>
                                    </div>
                                @empty
                                    <p class="text-zinc-400">No rounds scored yet.</p>
                                @endforelse
                            </div>
                        </section>

                        <form method="POST" action="{{ route('staff.sessions.end', $session) }}">
                            @csrf
                            <button class="w-full rounded-3xl bg-white px-6 py-4 text-2xl font-black text-black hover:bg-zinc-200">
                                Finish Game
                            </button>
                        </form>
                    @else
                        <section class="rounded-3xl border border-yellow-400/30 bg-yellow-400/10 p-5">
                            <p class="text-sm font-black uppercase tracking-wider text-yellow-300">Names first</p>
                            <h3 class="mt-2 text-3xl font-black">Timer is live</h3>
                            <p class="mt-2 text-sm text-zinc-300">Add players and save teams. The scoring buttons will appear automatically after teams are saved.</p>
                            <button type="button" onclick="openPlayerSetupModal()" class="mt-4 w-full rounded-3xl bg-white px-6 py-4 text-xl font-black text-black hover:bg-zinc-200">
                                Add Players
                            </button>
                        </section>
                    @endif
                </aside>
            </section>
        @elseif ($session->status === 'finished')
            @php
                $teams = $session->teams->sortByDesc('total_score')->values();
            @endphp

            <section class="grid min-h-0 flex-1 gap-4 overflow-visible lg:grid-cols-[1fr_380px]">
                <div class="android-scroll-safe rounded-3xl border border-white/10 bg-zinc-900 p-6 text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-red-400">Final result</p>
                    <h2 class="mt-2 text-5xl font-black">{{ $session->winner_team_name }}</h2>
                    <p class="mt-2 text-xl text-zinc-300">{{ $session->winner_team_name === 'Draw' ? 'It ended level. Rematch required.' : 'Stone cold winners.' }}</p>
                    <p class="mt-3 inline-flex rounded-full bg-red-600 px-5 py-2 text-sm font-black uppercase tracking-wider">
                        Bragging rights activated
                    </p>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($session->teams as $team)
                            <div class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600' : 'bg-yellow-400 text-black' }} p-5">
                                <p class="text-xl font-black">{{ $team->name }}</p>
                                <p class="mt-2 text-7xl font-black">{{ $team->total_score }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col gap-3 md:flex-row md:justify-center">
                        <a href="{{ route('share.show', $session->share_code) }}" class="rounded-2xl bg-white px-6 py-4 font-black text-black hover:bg-zinc-200">
                            Open Share Page
                        </a>
                    </div>
                </div>

                <aside class="thin-scrollbar android-scroll-safe rounded-3xl border border-white/10 bg-white/5 p-5">
                    <h3 class="text-2xl font-black">Send scorecard</h3>
                    <p class="mt-2 text-sm text-zinc-400">Add emails for everyone who wants the result.</p>

                    <form method="POST" action="{{ route('play.emails', $session) }}" class="mt-4 space-y-2">
                        @csrf
                        @for ($i = 0; $i < 5; $i++)
                            <input name="emails[]" type="email" placeholder="Email {{ $i + 1 }}" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-3 font-bold text-white" @if($i === 0) required @endif>
                        @endfor

                        <label class="flex gap-3 rounded-2xl bg-black/40 p-3 text-left">
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
            <section class="flex min-h-0 flex-1 items-center justify-center rounded-3xl border border-white/10 bg-zinc-900 p-8 text-center">
                <div>
                    <h2 class="text-4xl font-black">This game is {{ $session->status }}</h2>
                    <p class="mt-6 inline-flex rounded-2xl bg-red-600 px-6 py-4 font-black">Please ask staff to reset this lane.</p>
                </div>
            </section>
        @endif
    </main>

    <script>
        let wakeLock = null;
        let cursorHideTimer = null;
        let setupResetTimer = null;
        let autoEndTriggered = false;
        let lobbyPlayers = [];
        let selectedAddTeam = 'auto';
        const lobbyStorageKey = 'playard-lobby-' + @json($resource->id) + '-' + @json($session?->id ?? 'none');
        const currentLaneState = document.querySelector('meta[name="lane-state"]')?.content || 'none';
        let laneStatePollTimer = null;

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

        function resetSetupAutoResetTimer() {
            clearTimeout(setupResetTimer);
        }

        function saveLobbyToDevice() {
            try {
                localStorage.setItem(lobbyStorageKey, JSON.stringify(lobbyPlayers));
            } catch (error) {
                console.warn('Could not save lobby locally', error);
            }
        }

        function restoreLobbyFromDevice() {
            try {
                const saved = localStorage.getItem(lobbyStorageKey);
                if (!saved) return;

                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed)) {
                    lobbyPlayers = parsed.filter(player => player && player.name && player.team);
                }
            } catch (error) {
                console.warn('Could not restore lobby locally', error);
            }
        }

        function clearSavedLobby() {
            try {
                localStorage.removeItem(lobbyStorageKey);
            } catch (error) {
                console.warn('Could not clear saved lobby', error);
            }
        }

        function startLaneStatePolling() {
            clearInterval(laneStatePollTimer);

            laneStatePollTimer = setInterval(() => {
                if (document.hidden) return;

                fetch(window.location.href, {
                    method: 'GET',
                    cache: 'no-store',
                    headers: {
                        'X-Playard-Lane-Poll': '1',
                    },
                })
                    .then(response => response.text())
                    .then(html => {
                        const match = html.match(/<meta name="lane-state" content="([^"]+)">/);
                        const nextLaneState = match ? match[1] : null;

                        if (nextLaneState && nextLaneState !== currentLaneState) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.warn('Lane state polling failed', error);
                    });
            }, 2000);
        }

        function updateConnectionIndicator(isOnline) {
            const indicator = document.getElementById('connectionIndicator');
            if (!indicator) return;

            indicator.textContent = isOnline ? '● Synced' : '● Syncing';
            indicator.classList.toggle('text-green-400', isOnline);
            indicator.classList.toggle('text-red-400', !isOnline);
        }

        function playEndSound() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                const audioContext = new AudioContext();
                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();

                oscillator.frequency.value = 880;
                oscillator.type = 'sine';
                oscillator.connect(gain);
                gain.connect(audioContext.destination);

                gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.25, audioContext.currentTime + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + 0.35);

                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.4);
            } catch (error) {
                console.warn('End sound unavailable', error);
            }
        }

        function openHowToPlayModal() {
            const modal = document.getElementById('howToPlayModal');
            if (!modal) return;
            modal.classList.remove('pointer-events-none', 'opacity-0');
            modal.classList.add('opacity-100');
        }

        function closeHowToPlayModal() {
            const modal = document.getElementById('howToPlayModal');
            if (!modal) return;
            modal.classList.add('pointer-events-none', 'opacity-0');
            modal.classList.remove('opacity-100');
        }

        function openPlayerSetupModal() {
            const modal = document.getElementById('playerSetupModal');
            if (!modal) return;
            modal.classList.remove('pointer-events-none', 'opacity-0');
            modal.classList.add('opacity-100');

            setTimeout(() => {
                const input = document.getElementById('playerNameInput');
                if (input) input.focus();
            }, 100);
        }

        function closePlayerSetupModal() {
            const modal = document.getElementById('playerSetupModal');
            if (!modal) return;
            modal.classList.add('pointer-events-none', 'opacity-0');
            modal.classList.remove('opacity-100');
            renderLobbyPlayers();
        }

        async function enterFullscreenMode() {
            const element = document.documentElement;

            try {
                if (element.requestFullscreen) {
                    await element.requestFullscreen();
                } else if (element.webkitRequestFullscreen) {
                    await element.webkitRequestFullscreen();
                }
            } catch (error) {
                console.warn('Fullscreen unavailable', error);
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

        function selectAddTeam(team) {
            selectedAddTeam = team;

            const buttons = {
                auto: document.getElementById('addTeamAutoButton'),
                one: document.getElementById('addTeamOneButton'),
                two: document.getElementById('addTeamTwoButton'),
            };

            Object.values(buttons).forEach((button) => {
                if (!button) return;
                button.classList.remove('bg-white', 'text-black');
                button.classList.add('bg-white/10');
            });

            if (buttons[team]) {
                buttons[team].classList.add('bg-white', 'text-black');
                buttons[team].classList.remove('bg-white/10');
            }
        }

        function addPlayer() {
            const input = document.getElementById('playerNameInput');
            if (!input) return;

            const name = input.value.trim();
            if (!name) {
                input.focus();
                input.classList.add('ring-4', 'ring-red-500');
                setTimeout(() => input.classList.remove('ring-4', 'ring-red-500'), 700);
                return;
            }

            const teamOneCount = lobbyPlayers.filter(player => player.team === 'one').length;
            const teamTwoCount = lobbyPlayers.filter(player => player.team === 'two').length;
            const team = selectedAddTeam === 'auto'
                ? (teamOneCount <= teamTwoCount ? 'one' : 'two')
                : selectedAddTeam;

            lobbyPlayers.push({ name, team });
            saveLobbyToDevice();

            const lastPlayerAdded = document.getElementById('lastPlayerAdded');
            const lastPlayerAddedName = document.getElementById('lastPlayerAddedName');
            const nextPlayerPrompt = document.getElementById('nextPlayerPrompt');

            if (lastPlayerAdded && lastPlayerAddedName) {
                lastPlayerAddedName.textContent = name + ' joined ' + (team === 'one' ? 'Team Red' : 'Team Yellow');
                lastPlayerAdded.classList.remove('hidden');
            }

            if (nextPlayerPrompt) {
                nextPlayerPrompt.textContent = 'Nice. Add the next player.';
            }

            input.value = '';
            input.placeholder = 'Add another player';
            resetSetupAutoResetTimer();
            renderLobbyPlayers();

            setTimeout(() => {
                input.focus();
            }, 50);
        }

        function removePlayer(index) {
            lobbyPlayers.splice(index, 1);
            resetSetupAutoResetTimer();
            saveLobbyToDevice();
            renderLobbyPlayers();
        }

        function movePlayer(index, team) {
            lobbyPlayers[index].team = team;
            resetSetupAutoResetTimer();
            saveLobbyToDevice();
            renderLobbyPlayers();
        }

        function randomiseTeams() {
            lobbyPlayers = lobbyPlayers
                .sort(() => Math.random() - 0.5)
                .map((player, index) => ({
                    ...player,
                    team: index % 2 === 0 ? 'one' : 'two'
                }));

            resetSetupAutoResetTimer();
            saveLobbyToDevice();
            renderLobbyPlayers();
        }

        function clearPlayers() {
            lobbyPlayers = [];

            const lastPlayerAdded = document.getElementById('lastPlayerAdded');
            const nextPlayerPrompt = document.getElementById('nextPlayerPrompt');

            if (lastPlayerAdded) lastPlayerAdded.classList.add('hidden');
            if (nextPlayerPrompt) nextPlayerPrompt.textContent = 'Add the first player to begin.';

            const input = document.getElementById('playerNameInput');
            if (input) {
                input.value = '';
                input.placeholder = 'Type name here';
                input.focus();
            }

            resetSetupAutoResetTimer();
            clearSavedLobby();
            renderLobbyPlayers();
        }

        function generateTeamNames() {
            const shuffled = [...premiumTeamNames].sort(() => Math.random() - 0.5);
            const teamOne = document.getElementById('teamOneName');
            const teamTwo = document.getElementById('teamTwoName');

            if (teamOne) teamOne.value = shuffled[0];
            if (teamTwo) teamTwo.value = shuffled[1] ?? 'Curl Power';
            resetSetupAutoResetTimer();
        }

        function renderLobbyPlayers() {
            const teamOneCards = document.getElementById('teamOneCards');
            const teamTwoCards = document.getElementById('teamTwoCards');
            const hiddenPlayers = document.getElementById('hiddenPlayers');
            const teamOneCount = document.getElementById('teamOneCount');
            const teamTwoCount = document.getElementById('teamTwoCount');
            const totalPlayerCount = document.getElementById('totalPlayerCount');

            if (!teamOneCards || !teamTwoCards || !hiddenPlayers) return;

            teamOneCards.innerHTML = '';
            teamTwoCards.innerHTML = '';
            hiddenPlayers.innerHTML = '';

            lobbyPlayers.forEach((player, index) => {
                const card = document.createElement('div');
                card.className = 'group flex items-center gap-2 rounded-full border border-white/10 bg-zinc-950 px-3 py-2 shadow-xl';
                card.innerHTML = `
                    <span class="avatar-chip flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-black text-black">${escapeHtml(player.name.charAt(0).toUpperCase())}</span>
                    <span class="max-w-[120px] truncate text-sm font-black">${escapeHtml(player.name)}</span>
                    <button type="button" onclick="movePlayer(${index}, '${player.team === 'one' ? 'two' : 'one'}')" class="rounded-full bg-white/10 px-2 py-1 text-[10px] font-black hover:bg-white/20">
                        ${player.team === 'one' ? '→ Yellow' : '→ Red'}
                    </button>
                    <button type="button" onclick="removePlayer(${index})" class="rounded-full bg-red-600/80 px-2 py-1 text-[10px] font-black hover:bg-red-500">
                        ×
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
            if (totalPlayerCount) totalPlayerCount.textContent = lobbyPlayers.length;

            if (oneCount === 0) {
                teamOneCards.innerHTML = '<p class="rounded-2xl bg-black/30 p-4 text-sm text-zinc-500">Waiting for Red players.</p>';
            }

            if (twoCount === 0) {
                teamTwoCards.innerHTML = '<p class="rounded-2xl bg-black/30 p-4 text-sm text-zinc-500">Waiting for Yellow players.</p>';
            }
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

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
                confetti({ particleCount: 4, angle: 60, spread: 55, origin: { x: 0 } });
                confetti({ particleCount: 4, angle: 120, spread: 55, origin: { x: 1 } });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            })();
        }

        function updateCountdown() {
            const el = document.getElementById('countdown');
            if (!el) return;

            const status = el.dataset.status;
            const pausedRemaining = parseInt(el.dataset.pausedRemaining || '0', 10);

            if (status === 'paused') {
                const minutes = Math.floor(pausedRemaining / 60).toString().padStart(2, '0');
                const seconds = (pausedRemaining % 60).toString().padStart(2, '0');
                el.textContent = `PAUSED ${minutes}:${seconds}`;
                el.classList.add('text-yellow-300');
                return;
            }

            const endsAt = new Date(el.dataset.endsAt).getTime();
            const now = new Date().getTime();
            const diff = Math.max(0, Math.floor((endsAt - now) / 1000));
            const minutes = Math.floor(diff / 60).toString().padStart(2, '0');
            const seconds = (diff % 60).toString().padStart(2, '0');

            el.textContent = `${minutes}:${seconds}`;

            if (diff === 0 && !autoEndTriggered) {
                autoEndTriggered = true;
                el.textContent = 'TIME UP';
                playEndSound();

                const autoEndForm = document.getElementById('autoEndForm');

                if (autoEndForm) {
                    setTimeout(() => autoEndForm.submit(), 1500);
                }
            }
        }

        function connectLaneRealtimeUpdates() {
            const resourceId = @json($resource->id);

            if (!window.Echo || !resourceId) {
                console.warn('Realtime updates are not ready yet.');
                updateConnectionIndicator(false);
                return;
            }

            window.Echo.channel(`lane.${resourceId}`)
                .subscribed(() => {
                    console.log('Connected to lane updates:', resourceId);
                    updateConnectionIndicator(true);
                })
                .error((error) => {
                    console.warn('Lane realtime connection error:', error);
                    updateConnectionIndicator(false);
                })
                .listen('.game.session.updated', (event) => {
                    console.log('Lane updated', event);
                    window.location.reload();
                });
        }

        window.addEventListener('online', () => {
            console.log('Tablet is online');
            connectLaneRealtimeUpdates();
        });

        window.addEventListener('offline', () => {
            updateConnectionIndicator(false);
            showConnectionStatus();
        });

        document.addEventListener('visibilitychange', async () => {
            if (document.visibilityState === 'visible') {
                await requestWakeLock();
            }
        });

        document.addEventListener('mousemove', resetCursorTimer);
        document.addEventListener('touchstart', resetCursorTimer);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeHowToPlayModal();
                closePlayerSetupModal();
            }
        });

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

            protectKioskNavigation();
            resetCursorTimer();
            updateConnectionIndicator(false);
            selectAddTeam('auto');
            restoreLobbyFromDevice();
            renderLobbyPlayers();
            selectPoints('0');
            updateCountdown();
            setInterval(updateCountdown, 1000);
            setTimeout(connectLaneRealtimeUpdates, 500);
            startLaneStatePolling();
            requestWakeLock();

            const setupForms = document.querySelectorAll('form[action="{{ $session ? route('play.setup', $session) : '#' }}"]');
            setupForms.forEach((form) => {
                form.addEventListener('submit', () => {
                    clearSavedLobby();
                });
            });

            @if ($session && $session->status === 'finished')
                launchWinnerConfetti();
            @endif
        });
    </script>