<?php

namespace App\Domain\Practice;

class StreakCalculator
{
    public function calculate(iterable $rows, int $today): array
    {
        $done = [];
        foreach ($rows as $r) {
            if ($r->completed_on_time) {
                $done[$r->day] = true;
            }
        }$best = $run = 0;
        for ($d = 1; $d <= $today; $d++) {
            if (isset($done[$d])) {
                $run++;
                $best = max($best, $run);
            } elseif ($d < $today) {
                $run = 0;
            }
        }

return ['current' => $run, 'best' => $best];
    }
}
