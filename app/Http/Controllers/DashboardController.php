<?php

namespace App\Http\Controllers;

use App\Domain\Practice\ChallengeCatalog;
use App\Domain\Practice\StreakCalculator;
use App\Models\ChallengeProgress;
use App\Models\PracticeProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ChallengeCatalog $c, StreakCalculator $s): View
    {
        $p = PracticeProfile::firstOrCreate([], ['started_on' => today(), 'timezone' => config('app.timezone')]);
        $day = min(60, (int) $p->started_on->diffInDays(today()) + 1);
        $rows = ChallengeProgress::all()->keyBy('day');

        return view('dashboard', ['challenges' => $c->all(), 'today' => $day, 'progress' => $rows, 'stats' => $s->calculate($rows, $day)]);
    }
}
