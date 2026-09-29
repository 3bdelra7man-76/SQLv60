<?php

namespace Tests\Unit\Domain\Practice;

use App\Domain\Practice\ChallengeCatalog;
use App\Domain\Practice\DatasetCatalog;
use App\Domain\Practice\QueryRunner;
use PHPUnit\Framework\TestCase;

class ChallengeCatalogTest extends TestCase
{
    public function test_all_sixty_reference_queries_run_and_return_the_requested_columns(): void
    {
        $datasets = new DatasetCatalog;
        $catalog = new ChallengeCatalog($datasets);
        $runner = new QueryRunner($datasets);
        $challenges = $catalog->all();

        $this->assertCount(60, $challenges);
        foreach ($challenges as $day => $challenge) {
            $result = $runner->run($challenge['datasetKey'], $challenge['solution']);
            $this->assertTrue($result['ok'], "Day {$day}: ".($result['error'] ?? 'query failed'));
            $this->assertSame($challenge['columns'], $result['columns'], "Day {$day} columns differ");
            $this->assertNotEmpty($result['rows'], "Day {$day} has no answer rows");
        }
    }

    public function test_practice_runner_rejects_multiple_or_mutating_statements(): void
    {
        $runner = new QueryRunner(new DatasetCatalog);

        $this->assertFalse($runner->run('retail', 'SELECT 1; SELECT 2')['ok']);
        $this->assertFalse($runner->run('retail', 'DELETE FROM regions')['ok']);
    }
}
