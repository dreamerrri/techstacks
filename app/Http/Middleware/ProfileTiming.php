<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds X-Debug-* response headers with timing/query breakdown.
 * Only active when APP_DEBUG=true — harmless in production otherwise.
 *
 * Read these headers in the browser Network tab (click the main document
 * request) to distinguish:
 *   - High query time  -> database latency/indexes/cache issues
 *   - High total, low queries -> CPU (OPcache missing?) or slow external calls
 */
class ProfileTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('app.debug')) {
            return $next($request);
        }

        DB::enableQueryLog();
        $start = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $totalMs = (microtime(true) - $start) * 1000;
        $log = DB::getQueryLog();
        $queryMs = collect($log)->sum('time');

        $response->headers->set('X-Debug-Total-Ms', number_format($totalMs, 1));
        $response->headers->set('X-Debug-Query-Count', (string) count($log));
        $response->headers->set('X-Debug-Query-Ms', number_format($queryMs, 1));

        return $response;
    }
}
