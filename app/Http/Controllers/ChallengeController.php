<?php

namespace App\Http\Controllers;

use App\Domain\Practice\ChallengeCatalog;
use App\Domain\Practice\DatasetCatalog;
use App\Domain\Practice\QueryComparison;
use App\Domain\Practice\QueryRunner;
use App\Models\ChallengeProgress;
use App\Models\PracticeProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    public function show(int $day, ChallengeCatalog $c, DatasetCatalog $d): View
    {
        $p = PracticeProfile::firstOrCreate([], ['started_on' => today(), 'timezone' => config('app.timezone')]);
        $today = min(60, (int) $p->started_on->diffInDays(today()) + 1);
        abort_if($day < 1 || $day > $today, 404);
        $x = $c->find($day);

        return view('challenge', ['challenge' => $x, 'dataset' => $d->find($x['datasetKey']), 'progress' => ChallengeProgress::firstOrCreate(['day' => $day]), 'today' => $today]);
    }

    public function attempt(Request $r, int $day, ChallengeCatalog $c, QueryRunner $runner, QueryComparison $cmp): JsonResponse
    {
        $sql = $r->validate(['sql' => ['required', 'string', 'max:10000']])['sql'];
        $x = $c->find($day);
        $a = $runner->run($x['datasetKey'], $sql);
        $p = ChallengeProgress::firstOrCreate(['day' => $day]);
        $p->increment('attempts');
        if (! $a['ok']) {
            return response()->json($a, 422);
        }$e = $runner->run($x['datasetKey'], $x['solution']);
        $pass = $cmp->matches($a, $e, true);
        if ($pass && ! $p->completed_at) {
            $due = PracticeProfile::first()->started_on->addDays($day - 1);
            $p->update(['completed_at' => now(), 'completed_on_time' => today()->isSameDay($due)]);
        }

return response()->json($a + ['passed' => $pass, 'feedback' => $pass ? 'Perfect match!' : (count($a['rows']) !== count($e['rows']) ? 'Expected '.count($e['rows']).' rows.' : 'Check your columns, values, and ordering.'), 'solution' => $pass ? $x['solution'] : null]);
    }

    public function draft(Request $r, int $day): JsonResponse
    {
        $sql = $r->validate(['sql' => ['nullable', 'string', 'max:10000']])['sql'] ?? '';
        ChallengeProgress::firstOrCreate(['day' => $day])->update(['draft_sql' => $sql]);

        return response()->json(['saved' => true]);
    }
}
