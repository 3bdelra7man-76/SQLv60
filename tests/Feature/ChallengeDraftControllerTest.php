<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChallengeDraftControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_saves_a_draft_for_the_available_day(): void
    {
        $this->patchJson('/challenges/1/draft', ['sql' => 'SELECT name FROM regions'])
            ->assertOk()->assertJsonPath('saved', true);

        $this->assertDatabaseHas('challenge_progress', ['day' => 1, 'draft_sql' => 'SELECT name FROM regions']);
    }

    public function test_rejects_a_future_day_draft(): void
    {
        $this->patchJson('/challenges/2/draft', ['sql' => 'SELECT 1'])->assertNotFound();

        $this->assertDatabaseMissing('challenge_progress', ['day' => 2]);
    }
}
