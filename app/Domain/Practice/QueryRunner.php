<?php

namespace App\Domain\Practice;

use PDO;
use Throwable;

class QueryRunner
{
    public function __construct(private DatasetCatalog $datasets) {}

    /** @return array<string, mixed> */
    public function run(string $datasetKey, string $sql): array
    {
        $query = trim($sql);
        if (str_ends_with($query, ';')) {
            $query = trim(substr($query, 0, -1));
        }

        if (! preg_match('/^(SELECT|WITH)\b/i', $query)
            || str_contains($query, ';')
            || preg_match('/\b(ATTACH|DETACH|PRAGMA|VACUUM|CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|REPLACE)\b/i', $query)) {
            return ['ok' => false, 'error' => 'Use one read-only SELECT or WITH statement.'];
        }

        try {
            $database = new PDO('sqlite::memory:');
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dataset = $this->datasets->find($datasetKey);
            foreach ([...$dataset['schema'], ...$dataset['seed']] as $statement) {
                $database->exec($statement);
            }
            $database->exec('PRAGMA query_only=ON');

            $statement = $database->query($query);
            $columns = [];
            for ($index = 0; $index < $statement->columnCount(); $index++) {
                $columns[] = $statement->getColumnMeta($index)['name'];
            }

            $rows = [];
            while (count($rows) <= 250 && ($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $rows[] = $row;
            }

            return [
                'ok' => true,
                'columns' => $columns,
                'rows' => array_slice($rows, 0, 250),
                'truncated' => count($rows) > 250,
            ];
        } catch (Throwable $exception) {
            return ['ok' => false, 'error' => preg_replace('/SQLSTATE\[[^]]+\]:?\s*/', '', $exception->getMessage())];
        }
    }
}
