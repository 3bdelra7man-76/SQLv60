@extends('layout', ['title' => 'SQL Daily'])

@section('content')
    <section class="grid gap-5 md:grid-cols-3">
        <div class="rounded-3xl border border-blue-500/30 bg-blue-500/10 p-7 md:col-span-2">
            <p class="text-sm font-semibold tracking-wide text-blue-300">{{ $finished ? '60 DAYS COMPLETE' : 'TODAY · DAY '.$today }}</p>
            <h1 class="mt-2 text-4xl font-bold">{{ $finished ? 'Your SQL practice library is open.' : 'Keep the query streak alive.' }}</h1>
            <p class="mt-3 text-slate-300">
                {{ $finished ? 'Revisit any challenge whenever you want a syntax refresh.' : $challenges[$today]['title'].' · '.ucfirst($challenges[$today]['topic']) }}
            </p>
            <a class="mt-6 inline-block rounded-xl bg-blue-500 px-5 py-3 font-semibold text-white hover:bg-blue-400" href="{{ route('challenges.show', $today) }}">
                {{ $finished ? 'Browse the final challenge' : "Open today's challenge" }} →
            </a>
        </div>
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7">
            <p class="text-slate-400">Current streak</p>
            <p class="mt-2 text-5xl font-bold text-blue-400">{{ $stats['current'] }} 🔥</p>
            <p class="mt-4 text-sm text-slate-400">Best: {{ $stats['best'] }} days</p>
            <p class="mt-2 text-sm text-slate-400">Completed: {{ $completed }}/60 · Missed: {{ $missed }}</p>
        </div>
    </section>

    <h2 class="mt-10 text-xl font-semibold">Your 60 days</h2>
    <p class="mt-1 text-sm text-slate-400">Blue: completed · Outlined: available · Dimmed: upcoming</p>
    <div class="mt-4 grid grid-cols-5 gap-2 sm:grid-cols-10 lg:grid-cols-15">
        @foreach ($challenges as $day => $challenge)
            @php($done = isset($progress[$day]) && $progress[$day]->completed_at)
            <a
                @if ($day <= $today) href="{{ route('challenges.show', $day) }}" @endif
                title="Day {{ $day }} · {{ $challenge['title'] }}"
                @if ($day > $today) aria-disabled="true" @endif
                class="flex aspect-square items-center justify-center rounded-lg border text-sm transition-colors {{ $done ? 'border-blue-400 bg-blue-500 text-white' : ($day == $today && ! $finished ? 'border-blue-500 bg-blue-500/10 text-blue-300' : ($day <= $today ? 'border-slate-700 bg-slate-900 text-slate-300 hover:border-blue-500' : 'pointer-events-none border-slate-900 text-slate-600')) }}"
            >{{ $day }}</a>
        @endforeach
    </div>
@endsection
