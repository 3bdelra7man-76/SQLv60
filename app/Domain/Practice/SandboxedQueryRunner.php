<?php

namespace App\Domain\Practice;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class SandboxedQueryRunner
{
    /** @return array<string, mixed> */
    public function run(string $datasetKey, string $sql): array
    {
        $command = [PHP_BINARY];

        if (! extension_loaded('pdo_sqlite')) {
            array_push($command, '-d', 'extension=pdo_sqlite');
        }

        array_push($command, base_path('artisan'), 'app:execute-practice-query');
        $process = new Process($command, base_path());
        $process->setInput(json_encode(['dataset' => $datasetKey, 'sql' => $sql], JSON_THROW_ON_ERROR));
        $process->setTimeout(3);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return ['ok' => false, 'error' => 'This query took too long. Try a narrower query.'];
        }

        $result = json_decode($process->getOutput(), true);

        return is_array($result) && isset($result['ok'])
            ? $result
            : ['ok' => false, 'error' => 'The practice query could not be run.'];
    }
}
