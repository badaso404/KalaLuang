<?php

namespace App\Http\Controllers\Company;

use App\Enums\EducationLevel;
use App\Enums\EmploymentType;
use App\Enums\PaymentStatus;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\VacancyRequest;
use App\Models\Category;
use App\Models\JobVacancy;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyVacancyController extends Controller
{
    public function index(Request $request): View
    {
        return view('company.vacancies.index', [
            'vacancies' => $request->user()->company
                ->jobVacancies()
                ->with('category')
                ->withCount('applications')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('company.vacancies.create', $this->formData());
    }

    /**
     * Save the vacancy as pending_payment and raise its Rp100.000 invoice.
     */
    public function store(VacancyRequest $request): RedirectResponse
    {
        $company = $request->user()->company;

        $payment = DB::transaction(function () use ($request, $company): Payment {
            $vacancy = $company->jobVacancies()->create([
                ...$this->vacancyAttributes($request),
                'slug' => $this->uniqueSlug($request->validated('title')),
                'status' => VacancyStatus::PendingPayment,
            ]);

            return $this->createInvoice($vacancy);
        });

        return redirect()
            ->route('company.payments.show', $payment)
            ->with('status', 'Lowongan tersimpan. Selesaikan pembayaran agar lowongan dapat dikurasi admin.');
    }

    public function show(Request $request, JobVacancy $vacancy): View
    {
        $this->authorize('manage', $vacancy);

        return view('company.vacancies.show', [
            'vacancy' => $vacancy->load('category', 'payments'),
            'applicationCount' => $vacancy->applications()->count(),
        ]);
    }

    public function edit(JobVacancy $vacancy): View
    {
        $this->authorize('update', $vacancy);

        return view('company.vacancies.edit', [
            ...$this->formData(),
            'vacancy' => $vacancy,
        ]);
    }

    public function update(VacancyRequest $request, JobVacancy $vacancy): RedirectResponse
    {
        $this->authorize('update', $vacancy);

        $vacancy->update($this->vacancyAttributes($request));

        return redirect()
            ->route('company.vacancies.show', $vacancy)
            ->with('status', 'Lowongan berhasil diperbarui.');
    }

    /**
     * Close an active vacancy early.
     */
    public function close(JobVacancy $vacancy): RedirectResponse
    {
        $this->authorize('manage', $vacancy);

        abort_unless($vacancy->status === VacancyStatus::Active, 403);

        $vacancy->update(['status' => VacancyStatus::Closed]);

        return back()->with('status', 'Lowongan telah ditutup.');
    }

    /**
     * Send a rejected vacancy back for curation. No second payment is required.
     */
    public function resubmit(JobVacancy $vacancy): RedirectResponse
    {
        $this->authorize('manage', $vacancy);

        abort_unless($vacancy->status === VacancyStatus::Rejected, 403);

        $hasPaid = $vacancy->payments()->where('status', PaymentStatus::Paid)->exists();

        abort_unless($hasPaid, 403);

        $vacancy->update([
            'status' => VacancyStatus::UnderReview,
            'review_note' => null,
        ]);

        return back()->with('status', 'Lowongan diajukan ulang untuk kurasi admin.');
    }

    /**
     * Extend an expired vacancy by raising a fresh invoice for another window.
     */
    public function renew(JobVacancy $vacancy): RedirectResponse
    {
        $this->authorize('manage', $vacancy);

        abort_unless(
            in_array($vacancy->status, [VacancyStatus::Expired, VacancyStatus::Closed], strict: true),
            403,
        );

        $payment = DB::transaction(function () use ($vacancy): Payment {
            $vacancy->update(['status' => VacancyStatus::PendingPayment]);

            return $this->createInvoice($vacancy);
        });

        return redirect()
            ->route('company.payments.show', $payment)
            ->with('status', 'Tagihan perpanjangan dibuat. Selesaikan pembayaran untuk menayangkan ulang lowongan.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'employmentTypes' => EmploymentType::cases(),
            'educationLevels' => [EducationLevel::D3, EducationLevel::D4, EducationLevel::S1],
            'listingPrice' => config('kalaluang.listing_price'),
            'listingDuration' => config('kalaluang.listing_duration_days'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function vacancyAttributes(VacancyRequest $request): array
    {
        $attributes = $request->vacancyAttributes();

        if ($attributes['is_remote']) {
            $attributes['location'] = null;
        }

        return $attributes;
    }

    protected function createInvoice(JobVacancy $vacancy): Payment
    {
        return $vacancy->payments()->create([
            'company_id' => $vacancy->company_id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'amount' => config('kalaluang.listing_price'),
            'status' => PaymentStatus::Unpaid,
        ]);
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (JobVacancy::withTrashed()->where('slug', $slug)->exists());

        return $slug;
    }
}
