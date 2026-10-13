<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CompanyVerificationStatus;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(CompanyVerificationStatus::class)],
        ]);

        return view('admin.companies.index', [
            'companies' => Company::query()
                ->with('user')
                ->withCount('jobVacancies')
                ->when(
                    $filters['status'] ?? null,
                    fn ($query, string $status) => $query->where('verification_status', $status),
                    fn ($query) => $query->where('verification_status', CompanyVerificationStatus::Pending),
                )
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => CompanyVerificationStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function show(Company $company): View
    {
        return view('admin.companies.show', [
            'company' => $company->load('user', 'documents', 'verifier'),
        ]);
    }

    public function verify(Request $request, Company $company): RedirectResponse
    {
        $company->update([
            'verification_status' => CompanyVerificationStatus::Verified,
            'verification_note' => null,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        return back()->with('status', $company->name.' kini terverifikasi.');
    }

    public function reject(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'verification_note' => ['required', 'string', 'max:1000'],
        ], [], ['verification_note' => 'catatan alasan']);

        $company->update([
            'verification_status' => CompanyVerificationStatus::Rejected,
            'verification_note' => $validated['verification_note'],
            'verified_at' => null,
            'verified_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Verifikasi '.$company->name.' ditolak. Perusahaan dapat mengajukan ulang.');
    }

    /**
     * Suspend a company and take every one of its vacancies off the catalogue.
     */
    public function suspend(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'verification_note' => ['required', 'string', 'max:1000'],
        ], [], ['verification_note' => 'catatan alasan']);

        DB::transaction(function () use ($company, $validated, $request): void {
            $company->update([
                'verification_status' => CompanyVerificationStatus::Suspended,
                'verification_note' => $validated['verification_note'],
                'verified_by' => $request->user()->id,
            ]);

            $company->jobVacancies()
                ->where('status', VacancyStatus::Active)
                ->update(['status' => VacancyStatus::TakenDown]);
        });

        return back()->with('status', $company->name.' ditangguhkan dan seluruh lowongannya diturunkan.');
    }
}
