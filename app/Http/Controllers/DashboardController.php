<?php

namespace App\Http\Controllers;

use App\Domain\Practice\ChallengeCatalog;
use App\Domain\Practice\StreakCalculator;
use App\Models\ChallengeProgress;
use App\Models\PracticeProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ChallengeCatalog $catalog, StreakCalculator $streaks): View
    {
        $profile = PracticeProfile::firstOrCreate([], ['started_on' => today(), 'timezone' => config('app.timezone')]);
        $elapsedDay = max(1, (int) $profile->started_on->diffInDays(today()) + 1);
        $today = min(ChallengeCatalog::TOTAL_DAYS, $elapsedDay);
        $progress = ChallengeProgress::all()->keyBy('day');
        $completed = $progress->filter(fn (ChallengeProgress $row): bool => $row->completed_at !== null)->count();
        $dueDays = min(ChallengeCatalog::TOTAL_DAYS, $elapsedDay - 1);
        $timely = $progress->filter(fn (ChallengeProgress $row): bool => $row->completed_on_time)->count();

        return view('dashboard', [
            'challenges' => $catalog->all(),
            'today' => $today,
            'finished' => $elapsedDay > ChallengeCatalog::TOTAL_DAYS,
            'progress' => $progress,
            'completed' => $completed,
            'missed' => max(0, $dueDays - $timely),
            'stats' => $streaks->calculate($progress, min(ChallengeCatalog::TOTAL_DAYS + 1, $elapsedDay)),
        ]);
    }
}
