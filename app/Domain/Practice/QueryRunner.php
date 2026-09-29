<?php

namespace App\Domain\Practice;

use PDO;
use Throwable;

class QueryRunner
{
    public function __construct(private DatasetCatalog $datasets) {}

    public function run(string $key, string $sql): array
    {
        $q = rtrim(trim($sql), "; \n\r\t");
        if (! preg_match('/^(SELECT|WITH)\b/i', $q) || str_contains($q, ';') || preg_match('/\b(ATTACH|DETACH|PRAGMA|VACUUM|CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|REPLACE)\b/i', $q)) {
            return ['ok' => false, 'error' => 'Use one read-only SELECT or WITH statement.'];
        } try {
            $db = new PDO('sqlite::memory:');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $d = $this->datasets->find($key);
            foreach ([...$d['schema'], ...$d['seed']] as $s) {
                $db->exec($s);
            }$db->exec('PRAGMA query_only=ON');
            $st = $db->query($q);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $columns = $rows ? array_keys($rows[0]) : array_map(fn ($n) => $st->getColumnMeta($n)['name'], range(0, max(0, $st->columnCount() - 1)));

            return ['ok' => true, 'columns' => $columns, 'rows' => array_slice($rows, 0, 250), 'truncated' => count($rows) > 250];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => preg_replace('/SQLSTATE\[[^]]+\]:?\s*/', '', $e->getMessage())];
        }
    }
}
