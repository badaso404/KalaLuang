<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Models\Category;
use App\Models\JobVacancy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    /**
     * The public catalogue. Only vacancies from verified companies that are
     * still inside their paid window are listed (BR-03).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $vacancies = JobVacancy::query()
            ->published()
            ->with(['company', 'category'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->where(
                fn (Builder $match) => $match
                    ->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('description', 'like', '%'.$keyword.'%')
                    ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', '%'.$keyword.'%')),
            ))
            ->when($filters['employment_type'] ?? null, fn (Builder $query, string $type) => $query->where('employment_type', $type))
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query->whereHas(
                'category',
                fn (Builder $category) => $category->where('slug', $slug),
            ))
            ->when($filters['location'] ?? null, function (Builder $query, string $location) {
                return $location === 'remote'
                    ? $query->where('is_remote', true)
                    : $query->where('location', 'like', '%'.$location.'%');
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('vacancies.index', [
            'vacancies' => $vacancies,
            'categories' => Category::query()->orderBy('name')->get(),
            'employmentTypes' => EmploymentType::cases(),
            'locations' => JobVacancy::query()
                ->published()
                ->whereNotNull('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location'),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, JobVacancy $vacancy): View
    {
        abort_unless(
            JobVacancy::query()->published()->whereKey($vacancy->getKey())->exists(),
            404,
        );

        $vacancy->load(['company', 'category']);
        $user = $request->user();

        return view('vacancies.show', [
            'vacancy' => $vacancy,
            'hasApplied' => $user !== null && $vacancy->applications()
                ->where('user_id', $user->id)
                ->exists(),
            'hasSaved' => $user !== null && $vacancy->savedByUsers()
                ->where('user_id', $user->id)
                ->exists(),
            'canReport' => $user !== null && $user->can('report', $vacancy),
        ]);
    }
}
