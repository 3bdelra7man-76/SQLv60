<?php

namespace App\Domain\Practice;

use InvalidArgumentException;

class ChallengeCatalog
{
    public const TOTAL_DAYS = 60;

    public function __construct(private DatasetCatalog $datasets) {}

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $keys = array_keys($this->datasets->all());
        $order = [3, 10, 1, 7, 11, 4, 0, 9, 6, 2, 8, 5];
        $result = [];
        for ($day = 1; $day <= self::TOTAL_DAYS; $day++) {
            $result[$day] = $this->make($day, $keys[($day - 1) % 5], $order[($day - 1) % 12]);
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function find(int $day): array
    {
        return $this->all()[$day] ?? throw new InvalidArgumentException("Unknown day {$day}.");
    }

    /** @return array<string, mixed> */
    private function make(int $day, string $datasetKey, int $number): array
    {
        $d = $this->datasets->find($datasetKey);
        $m = $d['map'];
        $p = $m['parent'];
        $i = $m['item'];
        $f = $m['fact'];
        $pf = $m['parent_fk'];
        $if = $m['item_fk'];
        $pn = $m['parent_name'];
        $in = $m['item_name'];
        $s = [
            ['Focused filter', "Return every {$in} in the Accessories category, ordered alphabetically.", ['name', 'unit_price'], "SELECT name, unit_price FROM {$i} WHERE category = 'Accessories' ORDER BY name", 'Filter before ordering.', 'filtering'],
            ['Date-range activity', 'Return activity from February through March 2026, oldest first. Use id as a tie-breaker.', ['id', 'event_date', 'quantity', 'value'], "SELECT id, event_date, quantity, value FROM {$f} WHERE event_date BETWEEN '2026-02-01' AND '2026-03-31' ORDER BY event_date, id", 'BETWEEN includes both boundaries.', 'filtering'],
            ["Top {$in}s", "Return the three most expensive {$in}s.", ['name', 'unit_price'], "SELECT name, unit_price FROM {$i} ORDER BY unit_price DESC, name LIMIT 3", 'Sort before LIMIT.', 'filtering'],
            ['Keep empty groups', "Return every {$pn} and its activity count, including empty ones.", ["{$pn}_name", 'activity_count'], "SELECT p.name AS {$pn}_name, COUNT(f.id) AS activity_count FROM {$p} p LEFT JOIN {$f} f ON f.{$pf}=p.id GROUP BY p.id,p.name ORDER BY p.name", 'Use LEFT JOIN and count a fact column.', 'joins'],
            ['Totals by item', "Return total quantity and value for each active {$in}.", ["{$in}_name", 'total_quantity', 'total_value'], "SELECT i.name AS {$in}_name,SUM(f.quantity) AS total_quantity,SUM(f.value) AS total_value FROM {$i} i JOIN {$f} f ON f.{$if}=i.id GROUP BY i.id,i.name ORDER BY total_value DESC,i.name", 'Join, then group.', 'aggregation'],
            ['High-value groups', "Return {$pn}s with total activity value above 5,000.", ["{$pn}_name", 'total_value'], "SELECT p.name AS {$pn}_name,SUM(f.value) AS total_value FROM {$p} p JOIN {$f} f ON f.{$pf}=p.id GROUP BY p.id,p.name HAVING SUM(f.value)>5000 ORDER BY total_value DESC", 'Filter aggregates with HAVING.', 'aggregation'],
            ['Above average', "Return {$in}s priced above the average.", ['name', 'unit_price'], "SELECT name,unit_price FROM {$i} WHERE unit_price>(SELECT AVG(unit_price) FROM {$i}) ORDER BY unit_price DESC,name", 'Use a scalar subquery.', 'subquery'],
            ['Groups with activity', "Return {$pn}s with at least one activity row.", ['name'], "SELECT p.name FROM {$p} p WHERE EXISTS (SELECT 1 FROM {$f} f WHERE f.{$pf}=p.id) ORDER BY p.name", 'Correlate EXISTS with the parent id.', 'subquery'],
            ['Value bands', "Label each {$in} High at 2,000 or more, otherwise Standard.", ['name', 'price_band'], "SELECT name,CASE WHEN unit_price>=2000 THEN 'High' ELSE 'Standard' END AS price_band FROM {$i} ORDER BY name", 'Put CASE in SELECT.', 'case'],
            ['CTE totals', "Use a CTE to return {$pn} totals above 2,000.", ["{$pn}_name", 'total_value'], "WITH totals AS (SELECT {$pf},SUM(value) total_value FROM {$f} GROUP BY {$pf}) SELECT p.name AS {$pn}_name,t.total_value FROM totals t JOIN {$p} p ON p.id=t.{$pf} WHERE t.total_value>2000 ORDER BY t.total_value DESC,p.name", 'Group inside WITH, then join.', 'cte'],
            ['Rank within category', "Rank {$in}s by price inside each category; ties share a rank.", ['category', 'name', 'unit_price', 'price_rank'], "SELECT category,name,unit_price,DENSE_RANK() OVER (PARTITION BY category ORDER BY unit_price DESC) AS price_rank FROM {$i} ORDER BY category,price_rank,name", 'Use DENSE_RANK with PARTITION BY.', 'window'],
            ['Running value', 'Return each activity row with a running value total.', ['id', 'event_date', 'value', 'running_value'], "SELECT id,event_date,value,SUM(value) OVER (ORDER BY event_date,id) AS running_value FROM {$f} ORDER BY event_date,id", 'Order the window by date and id.', 'window'],
        ];
        [$title, $prompt, $columns, $solution, $hint, $topic] = $s[$number];

        return compact('day', 'datasetKey', 'title', 'prompt', 'columns', 'solution', 'hint', 'topic') + ['ordered' => true, 'explanation' => "A {$topic} solution for the {$d['name']} schema."];
    }
}
