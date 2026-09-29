<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChallengeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_today_workspace_shows_prompt_and_schema(): void
    {
        $this->get('/challenges/1')->assertOk()->assertSee('Keep empty groups')->assertSee('regions');
    }

    public function test_future_day_is_not_available(): void
    {
        $this->get('/challenges/2')->assertNotFound();
    }
}
