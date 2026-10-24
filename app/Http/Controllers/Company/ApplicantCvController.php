<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantCvController extends Controller
{
    /**
     * Serve the CV attached to an application to the company that posted the
     * vacancy. The path is the snapshot taken when the application was sent.
     */
    public function __invoke(Application $application): StreamedResponse
    {
        $this->authorize('manage', $application);

        abort_if(
            $application->cv_path === null || ! Storage::disk('local')->exists($application->cv_path),
            404,
        );

        return Storage::disk('local')->response($application->cv_path, 'cv.pdf', [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
