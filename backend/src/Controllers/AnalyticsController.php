<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AnalyticsRepository;

final class AnalyticsController
{
    private const EVENT_TYPES = ['page_view','product_view','product_click','search','whatsapp_click','quote_click'];
    public function __construct(private readonly AnalyticsRepository $analytics) {}

    public function store(Request $request): never
    {
        $data = $request->body();
        $eventType = (string)($data['event_type'] ?? '');
        $sessionId = (string)($data['session_id'] ?? '');
        $path = $this->text($data['path'] ?? '/', 500) ?: '/';
        if (!in_array($eventType, self::EVENT_TYPES, true) || !preg_match('/^[a-f0-9-]{16,36}$/i', $sessionId) || !str_starts_with($path, '/')) {
            Response::error('Evento de analytics inválido.', 422);
        }
        $device = (string)($data['device_type'] ?? 'unknown');
        if (!in_array($device, ['desktop','mobile','tablet','unknown'], true)) $device = 'unknown';
        $resultCount = isset($data['result_count']) && is_numeric($data['result_count']) ? max(0, min(100000, (int)$data['result_count'])) : null;
        $this->analytics->record([
            'event_type'=>$eventType,
            'session_id'=>$sessionId,
            'path'=>$path,
            'product_slug'=>$this->nullableText($data['product_slug'] ?? null, 210),
            'search_term'=>$this->nullableText($data['search_term'] ?? null, 190),
            'result_count'=>$resultCount,
            'target_url'=>$this->nullableText($data['target_url'] ?? null, 500),
            'referrer'=>$this->nullableText($data['referrer'] ?? null, 500),
            'device_type'=>$device,
        ]);
        Response::success(null, 'Evento registrado.', 201);
    }

    private function nullableText(mixed $value, int $limit): ?string
    {
        $text = $this->text($value, $limit);
        return $text === '' ? null : $text;
    }

    private function text(mixed $value, int $limit): string
    {
        return mb_substr(trim(is_string($value) ? $value : ''), 0, $limit);
    }
}
