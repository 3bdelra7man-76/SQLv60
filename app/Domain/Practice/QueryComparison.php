<?php

namespace App\Domain\Practice;

class QueryComparison
{
    public function matches(array $a, array $e, bool $ordered): bool
    {
        if (($a['columns'] ?? []) !== ($e['columns'] ?? [])) {
            return false;
        }$a = $a['rows'];
        $e = $e['rows'];
        if (! $ordered) {
            $n = fn ($r) => json_encode(array_values($r));
            $a = array_map($n, $a);
            $e = array_map($n, $e);
            sort($a);
            sort($e);
        }

return $a === $e;
    }
}
