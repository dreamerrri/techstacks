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

        // Average per-query latency reveals network round-trip problems;
        // the slowest single query reveals heavy scans / missing indexes.
        if ($log) {
            $avg = $queryMs / count($log);
            $response->headers->set('X-Debug-Query-Avg-Ms', number_format($avg, 1));

            $slowest = collect($log)->sortByDesc('time')->first();
            if ($slowest && $slowest['time'] > 10) {
                $safeQuery = preg_replace('/[^\x20-\x7E]/', '', $slowest['query']);
                $response->headers->set('X-Debug-Slowest-Ms', number_format($slowest['time'], 1));
                $response->headers->set('X-Debug-Slowest-Query', substr($safeQuery, 0, 200));
            }
        }

        return $response;
    }
}
