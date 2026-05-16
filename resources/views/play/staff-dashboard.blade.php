<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playard Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function updateStaffCountdowns() {
            document.querySelectorAll('[data-staff-countdown]').forEach((element) => {
                const endsAt = element.dataset.endsAt;
                const status = element.dataset.status;
                const pausedRemaining = parseInt(element.dataset.pausedRemaining || '0', 10);

                if (status === 'paused') {
                    const minutes = Math.floor(pausedRemaining / 60).toString().padStart(2, '0');
                    const seconds = (pausedRemaining % 60).toString().padStart(2, '0');
                    element.textContent = `PAUSED ${minutes}:${seconds}`;
                    return;
                }

                if (!endsAt) {
                    element.textContent = 'Not started';
                    return;
                }

                const endTime = new Date(endsAt).getTime();
                const now = new Date().getTime();
                const diff = Math.max(0, Math.floor((endTime - now) / 1000));
                const minutes = Math.floor(diff / 60).toString().padStart(2, '0');
                const seconds = (diff % 60).toString().padStart(2, '0');

                element.textContent = diff === 0 ? 'TIME UP' : `${minutes}:${seconds}`;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateStaffCountdowns();
            setInterval(updateStaffCountdowns, 1000);
        });
    </script>
</head>
<body class="min-h-screen bg-zinc-950 text-white">
    <main class="mx-auto max-w-7xl px-6 py-8">
        <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="inline-flex rounded-xl bg-red-600 px-4 py-2 text-2xl font-black tracking-tight">PLAYARD</div>
                <h1 class="mt-4 text-4xl font-black">Games Staff Dashboard</h1>
                <p class="mt-2 text-zinc-400">Super admin view for all active lane timers, staff controls and tablet screens.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('staff.customers.export') }}" class="rounded-2xl bg-green-500 px-5 py-3 text-center font-black text-black hover:bg-green-400">
                    Export CRM CSV
                </a>

                <a href="/admin" class="rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-center font-bold hover:bg-white/20">
                    Admin Panel
                </a>
            </div>
        </header>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <section class="mb-8 rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-black">Curling lanes</h2>
                    <p class="text-zinc-400">Choose 30 minutes or 60 minutes, launch a lane, monitor timers and export consented customer emails for Funbutler.</p>
                </div>
                <div class="rounded-full bg-red-600 px-4 py-2 text-sm font-black uppercase tracking-wide">
                    {{ $resources->count() }} active lanes
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($resources as $resource)
                @php
                    $activeSession = $resource->sessions->first();
                    $tabletUrl = route('play.tablet', $resource);
                @endphp

                <article class="rounded-3xl border border-white/10 bg-zinc-900 p-5 shadow-2xl">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-red-400">{{ $resource->activity->name }}</p>
                            <h3 class="text-3xl font-black">{{ $resource->name }}</h3>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-black uppercase {{ $activeSession ? 'bg-yellow-400 text-black' : 'bg-green-500 text-black' }}">
                            {{ $activeSession ? $activeSession->status : 'free' }}
                        </span>
                    </div>

                    @if ($activeSession)
                        <div class="mb-4 rounded-2xl bg-black/40 p-4">
                            <p class="text-sm text-zinc-400">Current game</p>
                            <p class="mt-1 font-bold">{{ $activeSession->duration_minutes }} minutes</p>
                            <p class="text-sm text-zinc-400">Share code: {{ $activeSession->share_code }}</p>

                            <div class="mt-4 rounded-2xl border border-white/10 bg-zinc-950 p-4 text-center">
                                <p class="text-xs font-black uppercase tracking-wider text-red-400">Timer</p>
                                <p
                                    class="mt-1 text-4xl font-black"
                                    data-staff-countdown
                                    data-ends-at="{{ optional($activeSession->ends_at)->toIso8601String() }}"
                                    data-status="{{ $activeSession->status }}"
                                    data-paused-remaining="{{ data_get($activeSession->metadata, 'remaining_seconds_when_paused', 0) }}"
                                >
                                    {{ $activeSession->started_at ? '--:--' : 'Not started' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            @if ($activeSession->status === 'playing')
                                <form method="POST" action="{{ route('staff.sessions.pause', $activeSession) }}">
                                    @csrf
                                    <button type="submit" class="w-full rounded-2xl bg-yellow-400 px-4 py-3 font-black text-black hover:bg-yellow-300">
                                        Pause Game
                                    </button>
                                </form>
                            @elseif ($activeSession->status === 'paused')
                                <form method="POST" action="{{ route('staff.sessions.resume', $activeSession) }}">
                                    @csrf
                                    <button type="submit" class="w-full rounded-2xl bg-green-500 px-4 py-3 font-black text-black hover:bg-green-400">
                                        Resume Game
                                    </button>
                                </form>
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                <form method="POST" action="{{ route('staff.sessions.add-time', $activeSession) }}">
                                    @csrf
                                    <input type="hidden" name="minutes" value="10">
                                    <button type="submit" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 font-black hover:bg-white/20">
                                        Add 10 min
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('staff.sessions.add-time', $activeSession) }}">
                                    @csrf
                                    <input type="hidden" name="minutes" value="30">
                                    <button type="submit" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 font-black hover:bg-white/20">
                                        Add 30 min
                                    </button>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('staff.sessions.end', $activeSession) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl bg-red-600 px-4 py-3 font-black hover:bg-red-500">
                                    End Game
                                </button>
                            </form>

                            <form method="POST" action="{{ route('staff.sessions.reset', $activeSession) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 font-black hover:bg-white/20">
                                    Reset Lane
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('staff.resources.start', $resource) }}" class="grid gap-3">
                            @csrf
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-zinc-300">Game duration</span>
                                <select name="duration_minutes" class="w-full rounded-2xl border border-white/10 bg-black px-4 py-3 font-bold text-white">
                                    <option value="30">30 minutes</option>
                                    <option value="60">60 minutes</option>
                                </select>
                            </label>

                            <button type="submit" class="rounded-2xl bg-red-600 px-4 py-3 font-black hover:bg-red-500">
                                Launch {{ $resource->name }}
                            </button>

                            <a href="{{ $tabletUrl }}" class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center font-black hover:bg-white/20">
                                Open Tablet Page
                            </a>
                        </form>
                    @endif
                </article>
            @empty
                <div class="rounded-3xl border border-white/10 bg-white/5 p-8 md:col-span-2 xl:col-span-4">
                    <h3 class="text-2xl font-black">No lanes found</h3>
                    <p class="mt-2 text-zinc-400">Go to Admin Panel → Resources / Lanes and add Curling Lane 1.</p>
                </div>
            @endforelse
        </section>
    </main>
</body>
</html>