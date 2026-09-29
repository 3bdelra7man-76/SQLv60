<?php

namespace Tests\Unit\Domain\Practice;

use App\Domain\Practice\StreakCalculator;
use PHPUnit\Framework\TestCase;

class StreakCalculatorTest extends TestCase
{
    public function test_missed_day_resets_current_streak_but_keeps_best_streak(): void
    {
        $rows = collect([
            (object) ['day' => 1, 'completed_on_time' => true],
            (object) ['day' => 2, 'completed_on_time' => true],
            (object) ['day' => 3, 'completed_on_time' => false],
            (object) ['day' => 4, 'completed_on_time' => true],
        ]);

        $this->assertSame(['current' => 1, 'best' => 2], (new StreakCalculator)->calculate($rows, 4));
    }
}
