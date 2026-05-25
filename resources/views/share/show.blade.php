<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playard Game Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>
<body class="min-h-screen bg-zinc-950 text-white">
    @php
        $teams = $session->teams->sortByDesc('total_score')->values();
        $winner = $teams->first();
        $runnerUp = $teams->skip(1)->first();
        $shareUrl = route('share.show', $session->share_code);
        $shareTextOptions = [
            'We just played curling at Playard and the scoreboard has receipts.',
            'Curling happened at Playard. Bragging rights may be involved.',
            'We played curling at Playard. The result is dramatic and possibly controversial.',
            'Fresh from the Playard curling lane. Come and judge the score yourself.',
            'The Playard curling result is in. Some people are handling it better than others.',
        ];
        $shareText = urlencode($shareTextOptions[array_rand($shareTextOptions)]);
        $winnerName = $session->winner_team_name ?: ($winner?->name ?? 'Game Results');
        $winnerScore = $winner?->total_score ?? 0;
        $runnerUpScore = $runnerUp?->total_score ?? 0;
        $scoreDifference = abs($winnerScore - $runnerUpScore);
        $totalRounds = $session->rounds->count();

        $drawBadges = [
            'No winners, maximum drama',
            'Unfinished business detected',
            'The lane refused to pick a side',
            'A very suspicious draw',
            'Tension level: Playard certified',
        ];

        $bigWinBadges = [
            'Absolute lane domination',
            'Public curling announcement',
            'Mercy was not available',
            'Main character performance',
            'The target has been conquered',
        ];

        $comfortableWinBadges = [
            'Comfortable bragging rights',
            'Controlled chaos, tidy win',
            'A calm and annoying victory',
            'Quietly ruthless',
            'The lane was handled',
        ];

        $closeWinBadges = [
            'Won by pure nerve',
            'One point. Many opinions.',
            'Tiny margin, massive ego boost',
            'A win is a win',
            'Nervy scenes at Playard',
        ];

        $defaultBadges = [
            'Stone cold winners',
            'Bragging rights unlocked',
            'Certified lane legends',
            'Winners by appointment',
            'The scoreboard has spoken',
        ];

        $drawVerdicts = [
            'This one ended level. Nobody gets peace until there is a rematch.',
            'A draw. The scoreboard shrugged and walked away.',
            'Level scores. Both teams are now legally required to argue about the closest stone.',
            'No winner today. Just unfinished business and suspicious confidence from both sides.',
            'A perfectly balanced game, which is another way of saying everyone is still talking too much.',
        ];

        $bigWinVerdicts = [
            $winnerName . ' did not come to make friends. They came to empty the lane.',
            $winnerName . ' treated this like a training drill and everyone else like background extras.',
            $winnerName . ' put on a curling clinic. Attendance was not optional.',
            $winnerName . ' won so clearly the scoreboard may need a lie down.',
            $winnerName . ' slid, scored, and left emotional damage on the lane.',
        ];

        $comfortableWinVerdicts = [
            $winnerName . ' kept it calm, clinical, and slightly annoying for everyone else.',
            $winnerName . ' never looked worried, which was rude but effective.',
            $winnerName . ' took control early and refused to give the drama department anything to work with.',
            $winnerName . ' won with the kind of confidence that makes opponents check the rules twice.',
            $winnerName . ' delivered a solid win and will probably mention it all evening.',
        ];

        $closeWinVerdicts = [
            $winnerName . ' escaped with the win. VAR would probably have been requested.',
            $winnerName . ' won by a margin so small it deserves its own investigation.',
            $winnerName . ' survived the pressure and will now pretend it was comfortable.',
            $winnerName . ' took the win by one point and immediately became impossible to live with.',
            $winnerName . ' won the tight one. The losing team is already preparing a speech about luck.',
        ];

        $defaultVerdicts = [
            $winnerName . ' took the win and the bragging rights are now legally binding.',
            $winnerName . ' got the job done. The lane has confirmed it in writing.',
            $winnerName . ' came, curled, conquered, and posed for the imaginary cameras.',
            $winnerName . ' secured the win. Complaints can be submitted directly to the scoreboard.',
            $winnerName . ' won fair and square, unless you ask the other team.',
        ];

        if ($session->winner_team_name === 'Draw') {
            $badge = $drawBadges[array_rand($drawBadges)];
            $verdict = $drawVerdicts[array_rand($drawVerdicts)];
        } elseif ($scoreDifference >= 6) {
            $badge = $bigWinBadges[array_rand($bigWinBadges)];
            $verdict = $bigWinVerdicts[array_rand($bigWinVerdicts)];
        } elseif ($scoreDifference >= 3) {
            $badge = $comfortableWinBadges[array_rand($comfortableWinBadges)];
            $verdict = $comfortableWinVerdicts[array_rand($comfortableWinVerdicts)];
        } elseif ($scoreDifference === 1) {
            $badge = $closeWinBadges[array_rand($closeWinBadges)];
            $verdict = $closeWinVerdicts[array_rand($closeWinVerdicts)];
        } else {
            $badge = $defaultBadges[array_rand($defaultBadges)];
            $verdict = $defaultVerdicts[array_rand($defaultVerdicts)];
        }
    @endphp

    <main class="mx-auto max-w-6xl px-5 py-8">
        <section id="scorecard" class="overflow-hidden rounded-[2rem] border border-white/10 bg-zinc-900 shadow-2xl">
            <div class="relative overflow-hidden bg-red-600 px-6 py-8 text-center">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 0, transparent 25%), radial-gradient(circle at 80% 10%, white 0, transparent 20%), radial-gradient(circle at 60% 90%, white 0, transparent 25%);"></div>
                <div class="relative mx-auto inline-flex rounded-xl bg-white px-5 py-2 text-3xl font-black tracking-tight text-red-600">PLAYARD</div>
                <p class="relative mt-3 text-sm font-black uppercase tracking-[0.3em] text-white/80">Curling Result</p>
            </div>

            <div class="p-6 md:p-10">
                <div class="text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-red-400">Winner</p>
                    <h1 class="mt-2 text-5xl font-black md:text-7xl">{{ $winnerName }}</h1>
                    <p class="mt-3 text-xl text-zinc-300">{{ $session->resource->name }} • {{ optional($session->ended_at)->format('d M Y') }}</p>
                    <div class="mt-5 inline-flex rounded-full bg-white px-5 py-2 text-sm font-black uppercase tracking-wider text-black">
                        {{ $badge }}
                    </div>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2">
                    @foreach ($session->teams as $team)
                        <div class="rounded-3xl border border-white/10 {{ $team->colour === 'red' ? 'bg-red-600' : 'bg-yellow-400 text-black' }} p-6 text-center shadow-xl">
                            <p class="text-2xl font-black">{{ $team->name }}</p>
                            <p class="mt-4 text-8xl font-black">{{ $team->total_score }}</p>
                            <div class="mt-4 text-sm font-bold opacity-80">
                                @foreach ($team->players as $player)
                                    <span>{{ $player->name }}</span>@if (! $loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 rounded-3xl border border-white/10 bg-black/40 p-6 text-center">
                    <p class="text-sm font-black uppercase tracking-wider text-red-400">Playard verdict</p>
                    <p class="mt-3 text-2xl font-black leading-snug">{{ $verdict }}</p>
                    <p class="mt-3 text-zinc-400">{{ $totalRounds }} rounds recorded. Bragging rights valid until the next rematch.</p>
                </div>

                <div class="mt-8 rounded-3xl bg-black/40 p-6">
                    <h2 class="text-2xl font-black">Round breakdown</h2>
                    <div class="mt-4 grid gap-3">
                        @forelse ($session->rounds->sortBy('round_number') as $round)
                            <div class="rounded-2xl bg-white/5 p-4">
                                <p class="font-black">Round {{ $round->round_number }}</p>
                                <p class="text-zinc-300">{{ $round->winningTeam?->name ?? 'No score' }} scored {{ $round->points }}</p>
                                <p class="mt-1 text-sm text-zinc-400">{{ $round->commentary }}</p>
                            </div>
                        @empty
                            <p class="text-zinc-400">No rounds were recorded.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-8 rounded-3xl border border-red-500/30 bg-red-500/10 p-6 text-center">
                    <p class="text-2xl font-black">Played at Playard Peterborough</p>
                    <p class="mt-2 text-zinc-300">Games. Drinks. Good times.</p>
                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl bg-black/30 px-4 py-3">
                            <p class="text-xs font-black uppercase tracking-wider text-red-300">Follow</p>
                            <p class="font-black">@playardpeterborough</p>
                        </div>
                        <div class="rounded-2xl bg-black/30 px-4 py-3">
                            <p class="text-xs font-black uppercase tracking-wider text-red-300">Book online</p>
                            <p class="font-black">www.playard.co.uk</p>
                        </div>
                        <div class="rounded-2xl bg-black/30 px-4 py-3">
                            <p class="text-xs font-black uppercase tracking-wider text-red-300">Tag us</p>
                            <p class="font-black">Show us the drama</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-3 md:grid-cols-6">
            <button onclick="copyShareLink()" class="rounded-2xl bg-white px-5 py-4 font-black text-black hover:bg-zinc-200">
                Copy Link
            </button>

            <button onclick="downloadCard('story-card', 'playard-curling-story.png')" class="rounded-2xl bg-red-600 px-5 py-4 font-black hover:bg-red-500">
                Download Story
            </button>

            <button onclick="downloadCard('square-card', 'playard-curling-post.png')" class="rounded-2xl bg-yellow-400 px-5 py-4 font-black text-black hover:bg-yellow-300">
                Download Post
            </button>

            <a href="https://wa.me/?text={{ $shareText }}%20{{ urlencode($shareUrl) }}" class="rounded-2xl bg-green-500 px-5 py-4 text-center font-black text-black hover:bg-green-400">
                WhatsApp
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" class="rounded-2xl bg-blue-600 px-5 py-4 text-center font-black hover:bg-blue-500">
                Facebook
            </a>

            <button onclick="window.print()" class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 font-black hover:bg-white/20">
                Print
            </button>
        </section>

        <p class="mt-4 text-center text-sm text-zinc-500">For Instagram, download the story or post image, then upload it and tag Playard.</p>

        <section class="mt-10 grid gap-8 lg:grid-cols-2">
            <div>
                <h2 class="mb-4 text-2xl font-black">Instagram Story image</h2>
                <div id="story-card" class="relative mx-auto flex aspect-[9/16] max-w-[390px] flex-col justify-between overflow-hidden rounded-[2rem] bg-zinc-950 p-7 text-white shadow-2xl ring-1 ring-white/10">
                    <div class="absolute inset-0 bg-gradient-to-b from-red-700 via-zinc-950 to-black"></div>
                    <div class="absolute -right-20 top-20 h-56 w-56 rounded-full bg-red-500/40 blur-3xl"></div>
                    <div class="absolute -left-24 bottom-24 h-64 w-64 rounded-full bg-yellow-400/20 blur-3xl"></div>

                    <div class="relative text-center">
                        <div class="mx-auto inline-flex rounded-xl bg-white px-5 py-2 text-3xl font-black text-red-600">PLAYARD</div>
                        <p class="mt-4 text-xs font-black uppercase tracking-[0.35em] text-red-200">Curling result</p>
                    </div>

                    <div class="relative text-center">
                        <p class="text-sm font-black uppercase tracking-wider text-yellow-300">Winner</p>
                        <h3 class="mt-3 text-5xl font-black leading-none">{{ $winnerName }}</h3>
                        <p class="mt-4 rounded-full bg-white px-4 py-2 text-sm font-black uppercase text-black">{{ $badge }}</p>
                    </div>

                    <div class="relative grid gap-3">
                        @foreach ($session->teams as $team)
                            <div class="rounded-3xl {{ $team->colour === 'red' ? 'bg-red-600 text-white' : 'bg-yellow-400 text-black' }} p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-lg font-black">{{ $team->name }}</p>
                                    <p class="text-5xl font-black">{{ $team->total_score }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="relative text-center">
                        <p class="text-lg font-black">{{ $session->resource->name }} at Playard</p>
                        <p class="mt-1 text-sm text-zinc-300">Games. Drinks. Good times.</p>
                        <div class="mt-5 rounded-3xl bg-white px-4 py-3 text-black">
                            <p class="text-xs font-black uppercase tracking-wider text-red-600">Book the comeback</p>
                            <p class="text-xl font-black">www.playard.co.uk</p>
                        </div>
                        <p class="mt-3 text-sm font-black text-zinc-200">Follow and tag @playardpeterborough</p>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-2xl font-black">Square social post image</h2>
                <div id="square-card" class="relative mx-auto flex aspect-square max-w-[520px] flex-col justify-between overflow-hidden rounded-[2rem] bg-zinc-950 p-8 text-white shadow-2xl ring-1 ring-white/10">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-700 via-zinc-950 to-black"></div>
                    <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-red-500/40 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-64 w-64 rounded-full bg-yellow-400/20 blur-3xl"></div>

                    <div class="relative flex items-center justify-between">
                        <div class="rounded-xl bg-white px-5 py-2 text-3xl font-black text-red-600">PLAYARD</div>
                        <p class="text-right text-xs font-black uppercase tracking-[0.25em] text-red-200">Curling<br>result</p>
                    </div>

                    <div class="relative text-center">
                        <p class="text-sm font-black uppercase tracking-wider text-yellow-300">Winner</p>
                        <h3 class="mt-2 text-5xl font-black leading-none">{{ $winnerName }}</h3>
                        <p class="mt-4 text-xl font-black text-zinc-200">{{ $verdict }}</p>
                    </div>

                    <div class="relative grid grid-cols-2 gap-4">
                        @foreach ($session->teams as $team)
                            <div class="rounded-3xl {{ $team->colour === 'red' ? 'bg-red-600 text-white' : 'bg-yellow-400 text-black' }} p-5 text-center">
                                <p class="text-lg font-black">{{ $team->name }}</p>
                                <p class="mt-2 text-6xl font-black">{{ $team->total_score }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="relative text-center">
                        <p class="font-black">{{ $session->resource->name }} • Playard Peterborough</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white px-4 py-3 text-black">
                                <p class="text-xs font-black uppercase tracking-wider text-red-600">Book now</p>
                                <p class="font-black">playard.co.uk</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-4 py-3">
                                <p class="text-xs font-black uppercase tracking-wider text-red-200">Follow</p>
                                <p class="font-black">@playardpeterborough</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function copyShareLink() {
            navigator.clipboard.writeText(@json($shareUrl));
            alert('Scorecard link copied');
        }

        async function downloadCard(elementId, filename) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const canvas = await html2canvas(element, {
                backgroundColor: null,
                scale: 3,
                useCORS: true,
            });

            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>
</body>
</html>