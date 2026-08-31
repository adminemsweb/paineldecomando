<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use PDO;

final class AnalyticsRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /** @param array<string,mixed> $event */
    public function record(array $event): void
    {
        if (in_array($event['event_type'], ['page_view','product_view'], true)) {
            $recent = $this->pdo->prepare('SELECT 1 FROM analytics_events WHERE session_id=:session_id AND event_type=:event_type AND path=:path AND created_at>=:recent LIMIT 1');
            $recent->execute([
                'session_id'=>$event['session_id'],
                'event_type'=>$event['event_type'],
                'path'=>$event['path'],
                'recent'=>(new DateTimeImmutable('-5 seconds'))->format('Y-m-d H:i:s'),
            ]);
            if ($recent->fetchColumn()) return;
        }
        $statement = $this->pdo->prepare('INSERT INTO analytics_events (event_type,session_id,path,product_slug,search_term,result_count,target_url,referrer,device_type) VALUES (:event_type,:session_id,:path,:product_slug,:search_term,:result_count,:target_url,:referrer,:device_type)');
        $statement->execute($event);

        // Keep anonymous interaction data only for the period needed by the dashboard.
        if (random_int(1, 200) === 1) {
            $cleanup = $this->pdo->prepare('DELETE FROM analytics_events WHERE created_at<:cutoff');
            $cleanup->execute([
                'cutoff'=>(new DateTimeImmutable('-180 days'))->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /** @return array<string,mixed> */
    public function report(int $days): array
    {
        $start = (new DateTimeImmutable("-{$days} days"))->setTime(0, 0)->format('Y-m-d H:i:s');
        $summary = $this->one('SELECT COUNT(DISTINCT session_id) visitors, SUM(CASE WHEN event_type=\'page_view\' THEN 1 ELSE 0 END) page_views, SUM(CASE WHEN event_type=\'product_view\' THEN 1 ELSE 0 END) product_views, SUM(CASE WHEN event_type=\'product_click\' THEN 1 ELSE 0 END) product_clicks, SUM(CASE WHEN event_type=\'search\' THEN 1 ELSE 0 END) searches, SUM(CASE WHEN event_type IN (\'whatsapp_click\',\'quote_click\') THEN 1 ELSE 0 END) conversions, SUM(CASE WHEN event_type=\'whatsapp_click\' THEN 1 ELSE 0 END) whatsapp_clicks, SUM(CASE WHEN event_type=\'quote_click\' THEN 1 ELSE 0 END) quote_clicks FROM analytics_events WHERE created_at>=:start', ['start'=>$start]);
        $summary = array_map(static fn($value) => (int)$value, $summary);
        $summary['conversion_rate'] = $summary['product_views'] > 0 ? round(($summary['conversions'] / $summary['product_views']) * 100, 1) : 0.0;

        return [
            'period_days'=>$days,
            'summary'=>$summary,
            'daily'=>$this->all('SELECT DATE(created_at) date, SUM(CASE WHEN event_type=\'page_view\' THEN 1 ELSE 0 END) page_views, SUM(CASE WHEN event_type=\'product_view\' THEN 1 ELSE 0 END) product_views, SUM(CASE WHEN event_type IN (\'whatsapp_click\',\'quote_click\') THEN 1 ELSE 0 END) conversions FROM analytics_events WHERE created_at>=:start GROUP BY DATE(created_at) ORDER BY date', ['start'=>$start]),
            'products'=>$this->all('SELECT e.product_slug slug, COALESCE(MAX(p.name),e.product_slug) name, SUM(CASE WHEN e.event_type=\'product_view\' THEN 1 ELSE 0 END) views, SUM(CASE WHEN e.event_type=\'product_click\' THEN 1 ELSE 0 END) clicks, SUM(CASE WHEN e.event_type IN (\'whatsapp_click\',\'quote_click\') THEN 1 ELSE 0 END) conversions FROM analytics_events e LEFT JOIN products p ON p.slug=e.product_slug AND p.deleted_at IS NULL WHERE e.created_at>=:start AND e.product_slug IS NOT NULL GROUP BY e.product_slug ORDER BY views DESC,clicks DESC LIMIT 10', ['start'=>$start]),
            'searches'=>$this->all('SELECT search_term term, COUNT(*) searches, SUM(CASE WHEN result_count=0 THEN 1 ELSE 0 END) without_results FROM analytics_events WHERE created_at>=:start AND event_type=\'search\' AND search_term IS NOT NULL GROUP BY search_term ORDER BY searches DESC LIMIT 10', ['start'=>$start]),
            'pages'=>$this->all('SELECT path,COUNT(*) views FROM analytics_events WHERE created_at>=:start AND event_type=\'page_view\' GROUP BY path ORDER BY views DESC LIMIT 10', ['start'=>$start]),
            'devices'=>$this->all('SELECT device_type device,COUNT(DISTINCT session_id) visitors FROM analytics_events WHERE created_at>=:start GROUP BY device_type ORDER BY visitors DESC', ['start'=>$start]),
        ];
    }

    /** @param array<string,mixed> $params @return array<string,mixed> */
    private function one(string $sql, array $params): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetch() ?: [];
    }

    /** @param array<string,mixed> $params @return list<array<string,mixed>> */
    private function all(string $sql, array $params): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }
}
