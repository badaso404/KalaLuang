@props(['title', 'heading' => null])

@php
    $links = [
        ['label' => 'Ringkasan', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
        ['label' => 'Verifikasi Perusahaan', 'url' => route('admin.companies.index'), 'active' => request()->routeIs('admin.companies.*')],
        ['label' => 'Konfirmasi Pembayaran', 'url' => route('admin.payments.index'), 'active' => request()->routeIs('admin.payments.*')],
        ['label' => 'Kurasi Lowongan', 'url' => route('admin.curation.index'), 'active' => request()->routeIs('admin.curation.*')],
        ['label' => 'Laporan', 'url' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*')],
    ];
@endphp

<x-panel :title="$title" :heading="$heading" :links="$links">
    {{ $slot }}
</x-panel>
