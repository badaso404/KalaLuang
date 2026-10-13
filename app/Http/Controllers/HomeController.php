<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobVacancy;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'categories' => Category::query()
                ->withCount(['jobVacancies as published_count' => fn ($query) => $query->published()])
                ->orderBy('name')
                ->get(),
            'latestVacancies' => JobVacancy::query()
                ->published()
                ->with(['company', 'category'])
                ->latest('published_at')
                ->take(6)
                ->get(),
            'publishedCount' => JobVacancy::query()->published()->count(),
        ]);
    }
}
