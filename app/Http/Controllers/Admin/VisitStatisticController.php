<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VisitStatisticController extends Controller
{
    /**
     * How many days the page summarises.
     */
    protected const WINDOW_DAYS = 30;

    /**
     * How many days the bar chart shows.
     */
    protected const CHART_DAYS = 14;

    public function __invoke(): View
    {
        $windowStart = now()->subDays(self::WINDOW_DAYS - 1)->startOfDay();

        return view('admin.visits.index', [
            'windowDays' => self::WINDOW_DAYS,
            'retentionDays' => (int) config('kalaluang.visit_retention_days'),
            'todayVisits' => Visit::query()->whereDate('created_at', today())->count(),
            'todayVisitors' => $this->uniqueVisitors(Visit::query()->whereDate('created_at', today())),
            'windowVisits' => Visit::query()->where('created_at', '>=', $windowStart)->count(),
            'windowVisitors' => $this->uniqueVisitors(Visit::query()->where('created_at', '>=', $windowStart)),
            'loggedInVisits' => Visit::query()
                ->where('created_at', '>=', $windowStart)
                ->whereNotNull('user_id')
                ->count(),
            'dailySeries' => $this->dailySeries(),
            'topPages' => $this->topPages($windowStart),
            'sources' => $this->sources($windowStart),
            'devices' => Visit::query()
                ->where('created_at', '>=', $windowStart)
                ->selectRaw('device, count(*) as total')
                ->groupBy('device')
                ->orderByDesc('total')
                ->get(),
        ]);
    }

    /**
     * Visits and unique visitors per day, with the quiet days filled in as
     * zeroes so the chart keeps an unbroken timeline.
     *
     * @return Collection<int, array{date: Carbon, visits: int, visitors: int}>
     */
    protected function dailySeries(): Collection
    {
        $start = now()->subDays(self::CHART_DAYS - 1)->startOfDay();

        $counts = Visit::query()
            ->where('created_at', '>=', $start)
            ->selectRaw($this->dateExpression().' as visit_date')
            ->selectRaw('count(*) as visits')
            ->selectRaw('count(distinct visitor_hash) as visitors')
            ->groupBy('visit_date')
            ->get()
            ->keyBy(fn (Visit $row) => (string) $row->visit_date);

        return Collection::times(self::CHART_DAYS, function (int $offset) use ($counts) {
            $date = now()->subDays(self::CHART_DAYS - $offset)->startOfDay();
            $row = $counts->get($date->toDateString());

            return [
                'date' => $date,
                'visits' => (int) ($row->visits ?? 0),
                'visitors' => (int) ($row->visitors ?? 0),
            ];
        });
    }

    /**
     * The most-opened pages inside the window.
     *
     * @return Collection<int, Visit>
     */
    protected function topPages(Carbon $windowStart): Collection
    {
        return Visit::query()
            ->where('created_at', '>=', $windowStart)
            ->selectRaw('path, count(*) as total')
            ->selectRaw('count(distinct visitor_hash) as visitors')
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * Where visitors came from. Visits without a referrer are reported as
     * direct traffic rather than hidden.
     *
     * @return array{direct: int, referrers: Collection<int, Visit>}
     */
    protected function sources(Carbon $windowStart): array
    {
        return [
            'direct' => Visit::query()
                ->where('created_at', '>=', $windowStart)
                ->whereNull('referrer_host')
                ->count(),
            'referrers' => Visit::query()
                ->where('created_at', '>=', $windowStart)
                ->whereNotNull('referrer_host')
                ->selectRaw('referrer_host, count(*) as total')
                ->groupBy('referrer_host')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
        ];
    }

    /**
     * @param  Builder<Visit>  $query
     */
    protected function uniqueVisitors(Builder $query): int
    {
        return $query->distinct()->count('visitor_hash');
    }

    /**
     * Truncating a timestamp to a date has no portable SQL spelling, so pick
     * the expression the current driver understands.
     */
    protected function dateExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => 'cast(created_at as date)',
            'sqlsrv' => 'cast(created_at as date)',
            default => 'date(created_at)',
        };
    }
}
