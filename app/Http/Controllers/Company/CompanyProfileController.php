<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('company.profile', [
            'company' => $request->user()->company,
        ]);
    }

    public function update(CompanyProfileRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        $attributes = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            if ($company?->logo_path !== null) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $attributes['logo_path'] = $request->file('logo')->store('company-logos', 'public');
        }

        if ($company === null) {
            $attributes['slug'] = Str::slug($attributes['name']).'-'.Str::lower(Str::random(6));
            $request->user()->company()->create($attributes);

            return redirect()->route('company.verification.show')
                ->with('status', 'Profil perusahaan tersimpan. Unggah dokumen legalitas untuk diverifikasi admin.');
        }

        $company->update($attributes);

        return back()->with('status', 'Profil perusahaan berhasil diperbarui.');
    }
}
