<?php

namespace App\Console\Commands;

use App\Domain\Practice\QueryRunner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:execute-practice-query')]
#[Description('Execute one isolated practice query')]
class ExecutePracticeQuery extends Command
{
    public function handle(QueryRunner $runner): int
    {
        $input = json_decode(stream_get_contents(STDIN), true);
        $result = is_array($input) && isset($input['dataset'], $input['sql'])
            ? $runner->run($input['dataset'], $input['sql'])
            : ['ok' => false, 'error' => 'Invalid query request.'];

        fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
