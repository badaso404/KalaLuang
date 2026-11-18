<?php

namespace App\Http\Middleware;

use App\Enums\DeviceType;
use App\Enums\UserRole;
use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Count page views of the public site for the admin statistics page. The row is
 * written in `terminate()` so the visitor never waits on it, and it holds no
 * personal data: the visitor is a salted hash of IP and user agent.
 */
class RecordVisit
{
    /**
     * Paths that are never counted: the admin panel itself, the health check,
     * and private file downloads.
     *
     * @var list<string>
     */
    protected array $except = [
        'admin',
        'admin/*',
        'up',
        'dokumen-perusahaan/*',
        'bukti-bayar/*',
    ];

    /**
     * Crawlers, preview bots, and uptime monitors. Counting them would drown
     * out the humans.
     */
    protected string $botPattern = '/bot|crawl|spider|slurp|facebookexternalhit|embedly|preview|monitor|curl|wget|headless|lighthouse|pingdom|python-requests|go-http-client/i';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Write the visit once the response has been sent.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldRecord($request, $response)) {
            return;
        }

        try {
            Visit::query()->create([
                'path' => '/'.ltrim($request->path(), '/'),
                'route_name' => $request->route()?->getName(),
                'visitor_hash' => $this->visitorHash($request),
                'device' => DeviceType::fromUserAgent($request->userAgent()),
                'referrer_host' => $this->referrerHost($request),
                'user_id' => $request->user()?->id,
            ]);
        } catch (Throwable $exception) {
            /** Statistics are never worth breaking a request over. */
            Log::warning('Gagal mencatat kunjungan.', ['exception' => $exception]);
        }
    }

    /**
     * Only successful, human, full-page loads of public pages are counted.
     */
    protected function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->is(...$this->except)) {
            return false;
        }

        if ($request->user()?->role === UserRole::Admin) {
            return false;
        }

        return preg_match($this->botPattern, (string) $request->userAgent()) !== 1;
    }

    /**
     * Salted with the application key so the hash cannot be reversed into an
     * IP address by anyone reading the table.
     */
    protected function visitorHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent(),
            config('app.key'),
        ]));
    }

    /**
     * The site that sent the visitor, ignoring navigation inside our own pages.
     */
    protected function referrerHost(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        if (! is_string($host) || $host === '' || $host === $request->getHost()) {
            return null;
        }

        return mb_substr($host, 0, 255);
    }
}
