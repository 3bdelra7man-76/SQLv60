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
        $category = match ($datasetKey) {
            'workforce' => 'Analyst',
            'subscriptions' => 'Egypt',
            default => 'Accessories',
        };
        $highThreshold = match ($datasetKey) {
            'workforce' => 200,
            'subscriptions' => 1000,
            'commerce' => 2000,
            default => 5000,
        };
        $cteThreshold = match ($datasetKey) {
            'workforce' => 150,
            'subscriptions', 'commerce' => 1000,
            default => 2000,
        };
        $s = [
            ['Focused filter', "Return every {$in} in the {$category} category, ordered alphabetically.", ['name', 'unit_price'], "SELECT name, unit_price FROM {$i} WHERE category = '{$category}' ORDER BY name", 'Filter before ordering.', 'filtering'],
            ['Date-range activity', 'Return activity from February through March 2026, oldest first. Use id as a tie-breaker.', ['id', 'event_date', 'quantity', 'value'], "SELECT id, event_date, quantity, value FROM {$f} WHERE event_date BETWEEN '2026-02-01' AND '2026-03-31' ORDER BY event_date, id", 'BETWEEN includes both boundaries.', 'filtering'],
            ["Top {$in}s", "Return the three most expensive {$in}s.", ['name', 'unit_price'], "SELECT name, unit_price FROM {$i} ORDER BY unit_price DESC, name LIMIT 3", 'Sort before LIMIT.', 'filtering'],
            ['Keep empty groups', "Return every {$pn} and its activity count, including empty ones.", ["{$pn}_name", 'activity_count'], "SELECT p.name AS {$pn}_name, COUNT(f.id) AS activity_count FROM {$p} p LEFT JOIN {$f} f ON f.{$pf}=p.id GROUP BY p.id,p.name ORDER BY p.name", 'Use LEFT JOIN and count a fact column.', 'joins'],
            ['Totals by item', "Return total quantity and value for each active {$in}.", ["{$in}_name", 'total_quantity', 'total_value'], "SELECT i.name AS {$in}_name,SUM(f.quantity) AS total_quantity,SUM(f.value) AS total_value FROM {$i} i JOIN {$f} f ON f.{$if}=i.id GROUP BY i.id,i.name ORDER BY total_value DESC,i.name", 'Join, then group.', 'aggregation'],
            ['High-value groups', "Return {$pn}s with total activity value above {$highThreshold}.", ["{$pn}_name", 'total_value'], "SELECT p.name AS {$pn}_name,SUM(f.value) AS total_value FROM {$p} p JOIN {$f} f ON f.{$pf}=p.id GROUP BY p.id,p.name HAVING SUM(f.value)>{$highThreshold} ORDER BY total_value DESC", 'Filter aggregates with HAVING.', 'aggregation'],
            ['Above average', "Return {$in}s priced above the average.", ['name', 'unit_price'], "SELECT name,unit_price FROM {$i} WHERE unit_price>(SELECT AVG(unit_price) FROM {$i}) ORDER BY unit_price DESC,name", 'Use a scalar subquery.', 'subquery'],
            ['Groups with activity', "Return {$pn}s with at least one activity row.", ['name'], "SELECT p.name FROM {$p} p WHERE EXISTS (SELECT 1 FROM {$f} f WHERE f.{$pf}=p.id) ORDER BY p.name", 'Correlate EXISTS with the parent id.', 'subquery'],
            ['Value bands', "Label each {$in} High at 2,000 or more, otherwise Standard.", ['name', 'price_band'], "SELECT name,CASE WHEN unit_price>=2000 THEN 'High' ELSE 'Standard' END AS price_band FROM {$i} ORDER BY name", 'Put CASE in SELECT.', 'case'],
            ['CTE totals', "Use a CTE to return {$pn} totals above {$cteThreshold}.", ["{$pn}_name", 'total_value'], "WITH totals AS (SELECT {$pf},SUM(value) total_value FROM {$f} GROUP BY {$pf}) SELECT p.name AS {$pn}_name,t.total_value FROM totals t JOIN {$p} p ON p.id=t.{$pf} WHERE t.total_value>{$cteThreshold} ORDER BY t.total_value DESC,p.name", 'Group inside WITH, then join.', 'cte'],
            ['Rank within category', "Rank {$in}s by price inside each category; ties share a rank.", ['category', 'name', 'unit_price', 'price_rank'], "SELECT category,name,unit_price,DENSE_RANK() OVER (PARTITION BY category ORDER BY unit_price DESC) AS price_rank FROM {$i} ORDER BY category,price_rank,name", 'Use DENSE_RANK with PARTITION BY.', 'window'],
            ['Running value', 'Return each activity row with a running value total.', ['id', 'event_date', 'value', 'running_value'], "SELECT id,event_date,value,SUM(value) OVER (ORDER BY event_date,id) AS running_value FROM {$f} ORDER BY event_date,id", 'Order the window by date and id.', 'window'],
        ];
        [$title, $prompt, $columns, $solution, $hint, $topic] = $s[$number];
        $variant = intdiv($day - 1, 12);

        if ($number === 3 && in_array($variant, [1, 3], true)) {
            $title = 'Join the activity trail';
            $prompt = "Return each activity id with its {$pn} and {$in} name, ordered by activity id.";
            $columns = ['id', "{$pn}_name", "{$in}_name"];
            $solution = "SELECT f.id,p.name AS {$pn}_name,i.name AS {$in}_name FROM {$f} f JOIN {$p} p ON p.id=f.{$pf} JOIN {$i} i ON i.id=f.{$if} ORDER BY f.id";
            $hint = 'Join the activity table to both lookup tables.';
        } elseif ($number === 3 && $variant === 4) {
            $title = 'Many-to-many pairs';
            $prompt = "Return each distinct {$pn} and {$in} pairing that appears in activity.";
            $columns = ["{$pn}_name", "{$in}_name"];
            $solution = "SELECT DISTINCT p.name AS {$pn}_name,i.name AS {$in}_name FROM {$p} p JOIN {$f} f ON f.{$pf}=p.id JOIN {$i} i ON i.id=f.{$if} ORDER BY p.name,i.name";
            $hint = 'The activity table connects the two sides.';
        }

        if ($number === 7 && in_array($variant, [1, 4], true)) {
            $title = 'Groups without activity';
            $prompt = "Return {$pn}s with no activity rows.";
            $solution = "SELECT p.name FROM {$p} p WHERE NOT EXISTS (SELECT 1 FROM {$f} f WHERE f.{$pf}=p.id) ORDER BY p.name";
            $hint = 'Use NOT EXISTS with a correlated subquery.';
        } elseif ($number === 7 && $variant === 2) {
            $title = 'Groups in the activity list';
            $prompt = "Use IN to return {$pn}s that appear in activity.";
            $solution = "SELECT name FROM {$p} WHERE id IN (SELECT {$pf} FROM {$f}) ORDER BY name";
            $hint = 'The subquery should return parent ids.';
        }

        if ($number === 8 && in_array($variant, [1, 3], true)) {
            $title = 'Zero instead of NULL';
            $prompt = "Return every {$pn} with its total activity value; show zero for {$pn}s without activity.";
            $columns = ["{$pn}_name", 'total_value'];
            $solution = "SELECT p.name AS {$pn}_name,COALESCE(SUM(f.value),0) AS total_value FROM {$p} p LEFT JOIN {$f} f ON f.{$pf}=p.id GROUP BY p.id,p.name ORDER BY p.name";
            $hint = 'A LEFT JOIN preserves empty groups; COALESCE replaces their NULL total.';
        }

        if ($number === 10 && in_array($variant, [1, 4], true)) {
            $title = 'Number each category';
            $prompt = "Give each {$in} a unique row number within its category, highest price first.";
            $columns = ['category', 'name', 'unit_price', 'row_number'];
            $solution = "SELECT category,name,unit_price,ROW_NUMBER() OVER (PARTITION BY category ORDER BY unit_price DESC,name) AS row_number FROM {$i} ORDER BY category,row_number";
            $hint = 'ROW_NUMBER never shares a number; include a tie-breaker.';
        } elseif ($number === 10 && $variant === 2) {
            $title = 'Rank with gaps';
            $prompt = "Rank {$in}s by price inside each category, leaving gaps after ties.";
            $columns = ['category', 'name', 'unit_price', 'price_rank'];
            $solution = "SELECT category,name,unit_price,RANK() OVER (PARTITION BY category ORDER BY unit_price DESC) AS price_rank FROM {$i} ORDER BY category,price_rank,name";
            $hint = 'RANK shares a rank for ties and leaves gaps.';
        }

        if ($number === 11 && in_array($variant, [1, 2], true)) {
            $function = $variant === 1 ? 'LAG' : 'LEAD';
            $direction = $variant === 1 ? 'previous' : 'next';
            $title = "{$direction} activity value";
            $prompt = "Return each activity row with the {$direction} row's value, ordered by date and id.";
            $columns = ['id', 'event_date', 'value', "{$direction}_value"];
            $solution = "SELECT id,event_date,value,{$function}(value) OVER (ORDER BY event_date,id) AS {$direction}_value FROM {$f} ORDER BY event_date,id";
            $hint = "{$function} looks at an adjacent row in the window order.";
        } elseif ($number === 11 && $variant === 3) {
            $title = 'Running value by group';
            $prompt = "Return each activity row with a running value total within its {$pn}.";
            $columns = ['id', $pf, 'event_date', 'value', 'running_value'];
            $solution = "SELECT id,{$pf},event_date,value,SUM(value) OVER (PARTITION BY {$pf} ORDER BY event_date,id) AS running_value FROM {$f} ORDER BY {$pf},event_date,id";
            $hint = 'PARTITION BY resets the running total for each group.';
        } elseif ($number === 11 && $variant === 4) {
            $title = 'Change from previous activity';
            $prompt = 'Return each activity row and its value change from the previous row, ordered by date and id.';
            $columns = ['id', 'event_date', 'value', 'value_change'];
            $solution = "SELECT id,event_date,value,value-LAG(value) OVER (ORDER BY event_date,id) AS value_change FROM {$f} ORDER BY event_date,id";
            $hint = 'Subtract LAG(value) from the current value.';
        }

        return compact('day', 'datasetKey', 'title', 'prompt', 'columns', 'solution', 'hint', 'topic') + ['ordered' => true, 'explanation' => "A {$topic} solution for the {$d['name']} schema."];
    }
}
