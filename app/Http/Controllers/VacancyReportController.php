<?php

namespace App\Http\Controllers;

use App\Enums\ReportReason;
use App\Models\JobVacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VacancyReportController extends Controller
{
    /**
     * File a report for admin review. One report per user per vacancy (BR-08).
     */
    public function store(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        $this->authorize('report', $vacancy);

        $validated = $request->validate([
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'reason' => 'alasan laporan',
            'description' => 'keterangan',
        ]);

        $vacancy->reports()->create([
            ...$validated,
            'reporter_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Laporan Anda telah dikirim dan akan ditinjau admin.');
    }
}
