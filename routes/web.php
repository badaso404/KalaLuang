<?php

use App\Http\Controllers\Admin\CompanyVerificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentConfirmationController;
use App\Http\Controllers\Admin\VacancyCurationController;
use App\Http\Controllers\Admin\VacancyReportReviewController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Company\ApplicantController;
use App\Http\Controllers\Company\CompanyDocumentController;
use App\Http\Controllers\Company\CompanyProfileController;
use App\Http\Controllers\Company\CompanyVacancyController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\PaymentController;
use App\Http\Controllers\Company\VerificationStatusController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobSeeker\ApplicationController;
use App\Http\Controllers\JobSeeker\JobSeekerProfileController;
use App\Http\Controllers\JobSeeker\SavedVacancyController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\VacancyReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/lowongan', [VacancyController::class, 'index'])->name('vacancies.index');
Route::get('/lowongan/{vacancy:slug}', [VacancyController::class, 'show'])->name('vacancies.show');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store']);
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/verifikasi-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('/verifikasi-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/verifikasi-email/kirim-ulang', EmailVerificationNotificationController::class)
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Pencari Kerja
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:job_seeker'])->prefix('pencari-kerja')->name('job-seeker.')->group(function () {
    Route::get('/profil', [JobSeekerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [JobSeekerProfileController::class, 'update'])->name('profile.update');

    Route::get('/lamaran', [ApplicationController::class, 'index'])->name('applications.index');

    Route::get('/tersimpan', [SavedVacancyController::class, 'index'])->name('saved.index');

    Route::middleware('verified')->group(function () {
        Route::post('/lowongan/{vacancy}/lamar', [ApplicationController::class, 'store'])->name('applications.store');
        Route::post('/lowongan/{vacancy}/simpan', [SavedVacancyController::class, 'store'])->name('saved.store');
        Route::delete('/lowongan/{vacancy}/simpan', [SavedVacancyController::class, 'destroy'])->name('saved.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Pelaporan Lowongan (pencari kerja & perusahaan)
|--------------------------------------------------------------------------
*/

Route::post('/lowongan/{vacancy}/laporkan', [VacancyReportController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:job_seeker,company'])
    ->name('vacancies.report');

/*
|--------------------------------------------------------------------------
| Dasbor Perusahaan
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:company'])->prefix('perusahaan')->name('company.')->group(function () {
    Route::get('/', [CompanyDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [CompanyProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [CompanyProfileController::class, 'update'])->name('profile.update');

    Route::get('/status-verifikasi', VerificationStatusController::class)->name('verification.show');
    Route::post('/dokumen', [CompanyDocumentController::class, 'store'])->name('documents.store');

    Route::get('/lowongan', [CompanyVacancyController::class, 'index'])->name('vacancies.index');
    Route::get('/lowongan/{vacancy}', [CompanyVacancyController::class, 'show'])->name('vacancies.show');
    Route::get('/lowongan/{vacancy}/ubah', [CompanyVacancyController::class, 'edit'])->name('vacancies.edit');
    Route::put('/lowongan/{vacancy}', [CompanyVacancyController::class, 'update'])->name('vacancies.update');
    Route::post('/lowongan/{vacancy}/tutup', [CompanyVacancyController::class, 'close'])->name('vacancies.close');
    Route::post('/lowongan/{vacancy}/ajukan-ulang', [CompanyVacancyController::class, 'resubmit'])->name('vacancies.resubmit');

    Route::middleware('company.verified')->group(function () {
        Route::get('/pasang-lowongan', [CompanyVacancyController::class, 'create'])->name('vacancies.create');
        Route::post('/pasang-lowongan', [CompanyVacancyController::class, 'store'])->name('vacancies.store');
        Route::post('/lowongan/{vacancy}/perpanjang', [CompanyVacancyController::class, 'renew'])->name('vacancies.renew');
    });

    Route::get('/tagihan/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/tagihan/{payment}/bukti-bayar', [PaymentController::class, 'storeProof'])->name('payments.proof');

    Route::get('/pelamar', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('/pelamar/{application}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::put('/pelamar/{application}/status', [ApplicantController::class, 'updateStatus'])->name('applicants.status');
});

/*
|--------------------------------------------------------------------------
| Panel Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/perusahaan', [CompanyVerificationController::class, 'index'])->name('companies.index');
    Route::get('/perusahaan/{company}', [CompanyVerificationController::class, 'show'])->name('companies.show');
    Route::post('/perusahaan/{company}/verifikasi', [CompanyVerificationController::class, 'verify'])->name('companies.verify');
    Route::post('/perusahaan/{company}/tolak', [CompanyVerificationController::class, 'reject'])->name('companies.reject');
    Route::post('/perusahaan/{company}/tangguhkan', [CompanyVerificationController::class, 'suspend'])->name('companies.suspend');

    Route::get('/pembayaran', [PaymentConfirmationController::class, 'index'])->name('payments.index');
    Route::post('/pembayaran/{payment}/konfirmasi', [PaymentConfirmationController::class, 'confirm'])->name('payments.confirm');
    Route::post('/pembayaran/{payment}/tolak', [PaymentConfirmationController::class, 'reject'])->name('payments.reject');

    Route::get('/kurasi', [VacancyCurationController::class, 'index'])->name('curation.index');
    Route::get('/kurasi/{vacancy}', [VacancyCurationController::class, 'show'])->name('curation.show');
    Route::post('/kurasi/{vacancy}/setujui', [VacancyCurationController::class, 'approve'])->name('curation.approve');
    Route::post('/kurasi/{vacancy}/tolak', [VacancyCurationController::class, 'reject'])->name('curation.reject');

    Route::get('/laporan', [VacancyReportReviewController::class, 'index'])->name('reports.index');
    Route::get('/laporan/{report}', [VacancyReportReviewController::class, 'show'])->name('reports.show');
    Route::post('/laporan/{report}/terbukti', [VacancyReportReviewController::class, 'uphold'])->name('reports.uphold');
    Route::post('/laporan/{report}/tolak', [VacancyReportReviewController::class, 'dismiss'])->name('reports.dismiss');
});
