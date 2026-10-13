<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\VacancyReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VacancyReportReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ReportStatus::class)],
        ]);

        return view('admin.reports.index', [
            'reports' => VacancyReport::query()
                ->with(['jobVacancy.company', 'reporter'])
                ->when(
                    $filters['status'] ?? null,
                    fn ($query, string $status) => $query->where('status', $status),
                    fn ($query) => $query->where('status', ReportStatus::Pending),
                )
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => ReportStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function show(VacancyReport $report): View
    {
        return view('admin.reports.show', [
            'report' => $report->load(['jobVacancy.company', 'reporter', 'reviewer']),
            'otherReports' => $report->jobVacancy
                ->reports()
                ->whereKeyNot($report->getKey())
                ->with('reporter')
                ->get(),
        ]);
    }

    /**
     * The report holds up: take the vacancy down from the catalogue.
     */
    public function uphold(Request $request, VacancyReport $report): RedirectResponse
    {
        abort_unless($report->status === ReportStatus::Pending, 403);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [], ['admin_note' => 'catatan admin']);

        DB::transaction(function () use ($report, $validated, $request): void {
            $report->update([
                'status' => ReportStatus::Valid,
                'admin_note' => $validated['admin_note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $report->jobVacancy->update(['status' => VacancyStatus::TakenDown]);
        });

        return redirect()
            ->route('admin.reports.index')
            ->with('status', 'Laporan terbukti. Lowongan telah diturunkan dari katalog.');
    }

    public function dismiss(Request $request, VacancyReport $report): RedirectResponse
    {
        abort_unless($report->status === ReportStatus::Pending, 403);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [], ['admin_note' => 'catatan admin']);

        $report->update([
            'status' => ReportStatus::Dismissed,
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.reports.index')
            ->with('status', 'Laporan ditandai tidak terbukti.');
    }
}
