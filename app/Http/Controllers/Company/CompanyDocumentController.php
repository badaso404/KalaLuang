<?php

namespace App\Http\Controllers\Company;

use App\Enums\CompanyVerificationStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyDocumentController extends Controller
{
    /**
     * Upload (or replace) a legality document. A rejected company that submits
     * a new document goes back to pending for another admin review.
     */
    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->company;

        abort_if($company === null, 404);

        $validated = $request->validate([
            'type' => ['required', Rule::enum(DocumentType::class)],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [], [
            'type' => 'jenis dokumen',
            'document' => 'dokumen',
        ]);

        $company->documents()->updateOrCreate(
            ['type' => $validated['type']],
            [
                'file_path' => $request->file('document')->store('company-documents', 'public'),
                'uploaded_at' => now(),
            ],
        );

        if ($company->verification_status === CompanyVerificationStatus::Rejected) {
            $company->update([
                'verification_status' => CompanyVerificationStatus::Pending,
                'verification_note' => null,
            ]);
        }

        return back()->with('status', 'Dokumen berhasil diunggah dan akan ditinjau admin.');
    }
}
