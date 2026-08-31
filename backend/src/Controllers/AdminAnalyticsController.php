<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthException;
use App\Repositories\AnalyticsRepository;
use App\Services\AuthService;

final class AdminAnalyticsController
{
    private const COOKIE = 'painel_session';
    public function __construct(private readonly AnalyticsRepository $analytics, private readonly AuthService $auth) {}

    public function index(Request $request): never
    {
        try { $this->auth->currentAdmin($request->cookie(self::COOKIE)); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }
        $days = (int)($request->query()['days'] ?? 30);
        if (!in_array($days, [7,30,90], true)) $days = 30;
        Response::success($this->analytics->report($days), 'Analytics carregado.');
    }
}
