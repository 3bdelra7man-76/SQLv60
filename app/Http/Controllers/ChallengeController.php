<?php

namespace App\Http\Controllers;

use App\Domain\Practice\ChallengeCatalog;
use App\Domain\Practice\DatasetCatalog;
use App\Domain\Practice\QueryComparison;
use App\Domain\Practice\SandboxedQueryRunner;
use App\Models\ChallengeProgress;
use App\Models\PracticeProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    public function show(int $day, ChallengeCatalog $catalog, DatasetCatalog $datasets): View
    {
        $today = $this->availableDay();
        abort_if($day < 1 || $day > $today, 404);
        $challenge = $catalog->find($day);

        return view('challenge', [
            'challenge' => $challenge,
            'dataset' => $datasets->find($challenge['datasetKey']),
            'progress' => ChallengeProgress::firstOrCreate(['day' => $day]),
            'today' => $today,
        ]);
    }

    public function attempt(Request $request, int $day, ChallengeCatalog $catalog, SandboxedQueryRunner $runner, QueryComparison $comparison): JsonResponse
    {
        abort_if($day < 1 || $day > $this->availableDay(), 404);
        $sql = $request->validate(['sql' => ['required', 'string', 'max:10000']])['sql'];
        $challenge = $catalog->find($day);
        $actual = $runner->run($challenge['datasetKey'], $sql);
        $progress = ChallengeProgress::firstOrCreate(['day' => $day]);
        $progress->increment('attempts');

        if (! $actual['ok']) {
            return response()->json($actual, 422);
        }

        $expected = $runner->run($challenge['datasetKey'], $challenge['solution']);
        if (! $expected['ok']) {
            return response()->json(['ok' => false, 'error' => 'This challenge is temporarily unavailable.'], 500);
        }

        $passed = ! $actual['truncated'] && $comparison->matches($actual, $expected, $challenge['ordered']);
        if ($passed && ! $progress->completed_at) {
            $dueDate = PracticeProfile::first()->started_on->addDays($day - 1);
            $progress->update(['completed_at' => now(), 'completed_on_time' => today()->isSameDay($dueDate)]);
        }

        return response()->json($actual + [
            'passed' => $passed,
            'feedback' => $passed ? 'Perfect match!' : $this->feedback($actual, $expected),
            'solution' => $passed ? $challenge['solution'] : null,
            'explanation' => $passed ? $challenge['explanation'] : null,
        ]);
    }

    public function draft(Request $request, int $day): JsonResponse
    {
        abort_if($day < 1 || $day > $this->availableDay(), 404);
        $sql = $request->validate(['sql' => ['nullable', 'string', 'max:10000']])['sql'] ?? '';
        ChallengeProgress::firstOrCreate(['day' => $day])->update(['draft_sql' => $sql]);

        return response()->json(['saved' => true]);
    }

    private function availableDay(): int
    {
        $profile = PracticeProfile::firstOrCreate([], ['started_on' => today(), 'timezone' => config('app.timezone')]);

        return min(60, max(1, (int) $profile->started_on->diffInDays(today()) + 1));
    }

    private function feedback(array $actual, array $expected): string
    {
        if ($actual['truncated']) {
            return 'Your query returned too many rows. Narrow the result.';
        }
        if ($actual['columns'] !== $expected['columns']) {
            return 'Check your output columns and aliases: '.implode(', ', $expected['columns']).'.';
        }
        if (count($actual['rows']) !== count($expected['rows'])) {
            return 'Expected '.count($expected['rows']).' rows.';
        }

        return 'The columns match, but one or more values or the row order differ.';
    }
}
