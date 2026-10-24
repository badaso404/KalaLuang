<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyDocumentDownloadController extends Controller
{
    /**
     * Serve a legality document to the company that uploaded it or to an
     * admin reviewing the verification request.
     */
    public function __invoke(CompanyDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->response($document->file_path, null, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
