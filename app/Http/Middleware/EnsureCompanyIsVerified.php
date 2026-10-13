<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsVerified
{
    /**
     * Keep unverified companies out of the posting flow and send them to the
     * verification status page instead (BR-02).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->user()?->company;

        if ($company === null) {
            return redirect()->route('company.profile.edit')
                ->with('status', 'Lengkapi profil perusahaan Anda terlebih dahulu.');
        }

        if (! $company->isVerified()) {
            return redirect()->route('company.verification.show')
                ->with('status', 'Perusahaan Anda harus terverifikasi sebelum dapat memasang lowongan.');
        }

        return $next($request);
    }
}
