<?php

namespace App\Domain\Practice;

class StreakCalculator
{
    /** @return array{current: int, best: int} */
    public function calculate(iterable $progressRows, int $today): array
    {
        $completedDays = [];
        foreach ($progressRows as $progress) {
            if ($progress->completed_on_time) {
                $completedDays[$progress->day] = true;
            }
        }

        $best = $current = 0;
        for ($day = 1; $day <= $today; $day++) {
            if (isset($completedDays[$day])) {
                $current++;
                $best = max($best, $current);
            } elseif ($day < $today) {
                $current = 0;
            }
        }

        return ['current' => $current, 'best' => $best];
    }
}
