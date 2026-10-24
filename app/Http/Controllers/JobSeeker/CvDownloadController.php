<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvDownloadController extends Controller
{
    /**
     * Serve the signed-in job seeker their own CV. The file lives on the
     * private disk, so it is only ever reachable through this endpoint.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $path = $request->user()->jobSeekerProfile?->cv_path;

        abort_if($path === null || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, 'cv.pdf', [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
