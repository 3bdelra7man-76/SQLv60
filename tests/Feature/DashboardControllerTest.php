<?php

namespace Tests\Feature;

use App\Models\PracticeProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_first_visit_starts_day_one_and_shows_the_challenge(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29)->startOfDay());

        $this->get('/')->assertOk()->assertSee('DAY 1')->assertSee('Keep empty groups');

        $this->assertDatabaseHas('practice_profiles', ['started_on' => '2026-09-29 00:00:00']);
    }

    public function test_later_visit_shows_the_new_daily_challenge(): void
    {
        PracticeProfile::create(['started_on' => '2026-09-27', 'timezone' => 'Africa/Cairo']);
        $this->travelTo(now()->setDate(2026, 9, 29)->startOfDay());

        $this->get('/')->assertOk()->assertSee('DAY 3');
    }
}
