<?php

namespace Tests\Feature;

use App\Models\PracticeProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChallengeAttemptControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_correct_query_completes_today_and_reveals_the_solution(): void
    {
        $sql = 'SELECT p.name AS region_name, COUNT(f.id) AS activity_count FROM regions p LEFT JOIN sales f ON f.region_id=p.id GROUP BY p.id,p.name ORDER BY p.name';

        $response = $this->postJson('/challenges/1/attempt', ['sql' => $sql]);
        $this->assertSame(200, $response->status(), $response->getContent());
        $response->assertJsonPath('passed', true)->assertJsonPath('solution', $sql);

        $this->assertDatabaseHas('challenge_progress', ['day' => 1, 'attempts' => 1, 'completed_on_time' => 1]);
    }

    public function test_mutating_query_is_rejected_without_completing_day(): void
    {
        $this->postJson('/challenges/1/attempt', ['sql' => 'DELETE FROM regions'])
            ->assertUnprocessable()->assertJsonPath('ok', false);

        $this->assertDatabaseHas('challenge_progress', ['day' => 1, 'attempts' => 1, 'completed_at' => null]);
    }

    public function test_future_day_cannot_be_completed_directly(): void
    {
        $this->postJson('/challenges/2/attempt', ['sql' => 'SELECT 1'])->assertNotFound();

        $this->assertDatabaseMissing('challenge_progress', ['day' => 2]);
    }

    public function test_late_completion_does_not_repair_the_streak(): void
    {
        PracticeProfile::create(['started_on' => today()->subDays(2), 'timezone' => 'Africa/Cairo']);
        $sql = 'SELECT p.name AS region_name, COUNT(f.id) AS activity_count FROM regions p LEFT JOIN sales f ON f.region_id=p.id GROUP BY p.id,p.name ORDER BY p.name';

        $this->postJson('/challenges/1/attempt', ['sql' => $sql])
            ->assertOk()->assertJsonPath('passed', true);

        $this->assertDatabaseHas('challenge_progress', ['day' => 1, 'completed_on_time' => 0]);
    }
}
