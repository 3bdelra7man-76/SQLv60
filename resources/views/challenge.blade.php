@extends('layout', ['title' => 'Day '.$challenge['day']])

@section('content')
    <a href="{{ route('dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300">← Dashboard</a>

    <div class="mt-5 grid gap-6 lg:grid-cols-2">
        <section>
            <p class="text-sm font-semibold tracking-wide text-blue-300">DAY {{ $challenge['day'] }} · {{ strtoupper($challenge['topic']) }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $challenge['title'] }}</h1>
            <p class="mt-4 text-lg text-slate-300">{{ $challenge['prompt'] }}</p>
            <p class="mt-3 text-sm text-slate-400">Required output: <code class="text-blue-300">{{ implode(', ', $challenge['columns']) }}</code></p>

            <div class="mt-7 rounded-2xl border border-slate-800 bg-slate-900/50 p-4">
                <h2 class="font-semibold">{{ $dataset['name'] }} schema</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $dataset['description'] }}</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($dataset['tables'] as $name => $columns)
                        <div class="rounded-xl border border-slate-700 bg-slate-900">
                            <h3 class="border-b border-slate-700 px-4 py-2 font-mono text-blue-300">{{ $name }}</h3>
                            @foreach ($columns as $column)
                                <div class="flex justify-between gap-2 px-4 py-1 text-sm">
                                    <span class="font-mono">{{ $column[0] }}</span>
                                    <span class="whitespace-nowrap text-slate-500">{{ $column[1] }} {{ $column[2] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    @foreach ($dataset['relations'] as $relation)
                        <span class="rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1 font-mono text-blue-200">{{ $relation }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <section>
            <div id="sql-workspace" data-day="{{ $challenge['day'] }}" data-sql="{{ $progress->draft_sql }}" class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="font-semibold">Your SQL</h2>
                    <span class="text-xs text-slate-500">Ctrl/⌘ + Enter to run</span>
                </div>
                <div id="editor" class="overflow-hidden rounded-xl border border-slate-700"></div>
                <div class="mt-4 flex items-center gap-3">
                    <button id="run" class="rounded-lg bg-blue-500 px-5 py-2 font-semibold text-white hover:bg-blue-400 disabled:opacity-50">Run query</button>
                    <button id="hint" class="text-sm text-slate-400 hover:text-blue-300">Show hint</button>
                </div>
                <p id="hint-text" class="mt-3 hidden text-sm text-blue-300">{{ $challenge['hint'] }}</p>
                <div id="feedback" class="mt-4 text-sm" role="status" aria-live="polite"></div>
                <div id="results" class="mt-4 overflow-auto"></div>

                @if ($progress->completed_at)
                    <div class="mt-6 border-t border-slate-700 pt-4">
                        <h3 class="font-semibold text-emerald-300">Completed · Reference solution</h3>
                        <pre class="solution">{{ $challenge['solution'] }}</pre>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
