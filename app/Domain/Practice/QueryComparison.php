<?php

namespace App\Domain\Practice;

class QueryComparison
{
    public function matches(array $actual, array $expected, bool $ordered): bool
    {
        if (($actual['columns'] ?? []) !== ($expected['columns'] ?? [])) {
            return false;
        }

        $actualRows = $actual['rows'];
        $expectedRows = $expected['rows'];
        if (! $ordered) {
            $normalize = fn (array $row): string => json_encode(array_values($row), JSON_THROW_ON_ERROR);
            $actualRows = array_map($normalize, $actualRows);
            $expectedRows = array_map($normalize, $expectedRows);
            sort($actualRows);
            sort($expectedRows);
        }

        return $actualRows === $expectedRows;
    }
}
