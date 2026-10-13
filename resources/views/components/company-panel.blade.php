@props(['title', 'heading' => null])

@php
    $links = [
        ['label' => 'Ringkasan', 'url' => route('company.dashboard'), 'active' => request()->routeIs('company.dashboard')],
        ['label' => 'Pasang Lowongan', 'url' => route('company.vacancies.create'), 'active' => request()->routeIs('company.vacancies.create')],
        ['label' => 'Lowongan Saya', 'url' => route('company.vacancies.index'), 'active' => request()->routeIs('company.vacancies.*') && ! request()->routeIs('company.vacancies.create')],
        ['label' => 'Pelamar', 'url' => route('company.applicants.index'), 'active' => request()->routeIs('company.applicants.*')],
        ['label' => 'Profil Perusahaan', 'url' => route('company.profile.edit'), 'active' => request()->routeIs('company.profile.*')],
        ['label' => 'Status Verifikasi', 'url' => route('company.verification.show'), 'active' => request()->routeIs('company.verification.*')],
    ];
@endphp

<x-panel :title="$title" :heading="$heading" :links="$links">
    {{ $slot }}
</x-panel>
