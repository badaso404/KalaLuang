<?php

namespace App\Http\Controllers\Company;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationStatusController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $company = $request->user()->company;

        if ($company === null) {
            return redirect()->route('company.profile.edit')
                ->with('status', 'Lengkapi profil perusahaan Anda terlebih dahulu.');
        }

        return view('company.verification', [
            'company' => $company->load('documents'),
            'documentTypes' => DocumentType::cases(),
            'hasNib' => $company->documents->contains('type', DocumentType::Nib),
        ]);
    }
}
