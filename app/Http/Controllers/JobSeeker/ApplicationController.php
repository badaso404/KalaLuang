<?php

namespace App\Http\Controllers\JobSeeker;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    /**
     * Urutan yang boleh dipilih pencari kerja pada riwayat lamaran.
     *
     * @var list<string>
     */
    protected const SORTS = ['terbaru', 'terlama', 'pembaruan'];

    /**
     * The application history: filterable by status and keyword, with the
     * database notifications that the company's status changes generated.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ApplicationStatus::class)],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
        ]);

        $user = $request->user();
        $sort = $filters['sort'] ?? 'terbaru';

        $statusCounts = $user->applications()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $statusCounts->sum();
        $awaitingResponse = (int) ($statusCounts[ApplicationStatus::New->value] ?? 0);

        // Pembaruan terbaru dari perusahaan. ID yang belum terbaca dikumpulkan
        // dulu supaya tetap bisa ditandai di antarmuka setelah dibaca di sini.
        $notifications = $user->notifications()->latest()->take(5)->get();
        $unreadNotificationIds = $notifications->whereNull('read_at')->pluck('id')->all();
        $user->unreadNotifications->markAsRead();

        return view('job-seeker.applications', [
            'applications' => $user->applications()
                // company.user dipakai untuk kontak PIC yang dibuka saat diterima.
                ->with(['jobVacancy.company.user', 'jobVacancy.category'])
                ->when(
                    $filters['status'] ?? null,
                    fn (Builder $query, string $status) => $query->where('status', $status),
                )
                ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->whereHas(
                    'jobVacancy',
                    fn (Builder $vacancy) => $vacancy
                        ->where('title', 'like', '%'.$keyword.'%')
                        ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', '%'.$keyword.'%')),
                ))
                ->tap(fn (Builder $query) => match ($sort) {
                    'terlama' => $query->oldest(),
                    'pembaruan' => $query->orderByRaw('status_updated_at is null, status_updated_at desc'),
                    default => $query->latest(),
                })
                ->paginate(10)
                ->withQueryString(),
            'statusCounts' => $statusCounts,
            'total' => $total,
            'awaitingResponse' => $awaitingResponse,
            'acceptedCount' => (int) ($statusCounts[ApplicationStatus::Accepted->value] ?? 0),
            'responseRate' => $total === 0 ? 0 : (int) round(($total - $awaitingResponse) / $total * 100),
            'notifications' => $notifications,
            'unreadNotificationIds' => $unreadNotificationIds,
            'sorts' => self::SORTS,
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }

    /**
     * Apply to a vacancy. Requires a complete profile (BR-06) and allows one
     * application per vacancy (BR-05). The CV is snapshotted onto the application.
     */
    public function store(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        abort_unless(
            JobVacancy::query()->published()->whereKey($vacancy->getKey())->exists(),
            404,
        );

        $user = $request->user();
        $profile = $user->jobSeekerProfile;

        if ($profile === null || ! $profile->isComplete()) {
            return redirect()->route('job-seeker.profile.edit')->with(
                'error',
                'Lengkapi data pendidikan dan unggah CV sebelum melamar.',
            );
        }

        if ($vacancy->applications()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda sudah pernah melamar lowongan ini.');
        }

        $validated = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ], [], ['cover_letter' => 'surat lamaran']);

        $vacancy->applications()->create([
            ...$validated,
            'user_id' => $user->id,
            'cv_path' => $profile->cv_path,
        ]);

        return redirect()
            ->route('job-seeker.applications.index')
            ->with('status', 'Lamaran Anda berhasil dikirim.');
    }
}
