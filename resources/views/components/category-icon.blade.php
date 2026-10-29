@props(['slug'])

{{--
    Ikon bidang digambar langsung sebagai primitif SVG (persegi, lingkaran, garis)
    agar tidak bergantung pada paket ikon eksternal. Slug yang belum punya ikon
    jatuh ke ikon tas kerja sebagai bawaan.
--}}
@php
    $shapes = match ($slug) {
        'administrasi' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/>',
        'pemasaran' => '<path d="M4 10v4l10 4V6L4 10Z"/><path d="M14 8h3a3 3 0 0 1 0 6h-3"/><path d="M8 15v4"/>',
        'teknologi-informasi' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 9l2 2-2 2M13 13h3M9 20h6"/>',
        'keuangan' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 12h.01M17 12h.01"/>',
        'desain' => '<path d="M12 3 8 9v9a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V9l-4-6Z"/><path d="M8 13h8"/><circle cx="12" cy="17" r="1"/>',
        'customer-service' => '<path d="M5 13v-2a7 7 0 0 1 14 0v2"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/><path d="M12 21h3a2 2 0 0 0 2-2"/>',
        'penjualan' => '<path d="M4 7h10l5 5-7 7-5-5V7Z"/><circle cx="8.5" cy="10.5" r="1.2"/>',
        'sumber-daya-manusia' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><circle cx="17" cy="9.5" r="2.2"/><path d="M14.5 19a4 4 0 0 1 6 0"/>',
        'operasional-logistik' => '<path d="M3 7h10v9H3V7Z"/><path d="M13 10h4l3 3v3h-7"/><circle cx="7" cy="18.5" r="1.8"/><circle cx="17" cy="18.5" r="1.8"/>',
        'produksi-manufaktur' => '<path d="M4 20V10l4 3V10l4 3V10l4 3V9l4 2v9H4Z"/><path d="M8 20v-3M16 20v-3"/>',
        'pendidikan-pelatihan' => '<path d="M3 9l9-4 9 4-9 4-9-4Z"/><path d="M7 11v5c0 1.3 2.2 2.5 5 2.5s5-1.2 5-2.5v-5"/><path d="M21 9v5"/>',
        'kesehatan' => '<path d="M12 21s-7-4.5-7-9.5A4.5 4.5 0 0 1 12 8a4.5 4.5 0 0 1 7 3.5c0 5-7 9.5-7 9.5Z"/><path d="M12 11v4M10 13h4"/>',
        'hukum' => '<path d="M12 4v16M7 20h10"/><path d="M4 9h16"/><path d="M4 9l-1.5 4a3 3 0 0 0 5 0L6 9"/><path d="M20 9l-1.5 4a3 3 0 0 0 5 0L22 9"/>',
        'media-konten' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M10 9.5v5l4-2.5-4-2.5Z"/>',
        'teknik-konstruksi' => '<path d="M4 18h16v3H4v-3Z"/><path d="M12 4a7 7 0 0 0-7 7v7h14v-7a7 7 0 0 0-7-7Z"/><path d="M12 4v7M9 11V5.5M15 11V5.5"/>',
        'data-riset' => '<path d="M4 20V4"/><path d="M4 20h16"/><rect x="7" y="12" width="3" height="5"/><rect x="12" y="8" width="3" height="9"/><rect x="17" y="5" width="3" height="12"/>',
        'pariwisata-perhotelan' => '<rect x="3" y="8" width="18" height="11" rx="2"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/><path d="M3 13h18"/>',
        'pertanian-pangan' => '<path d="M12 21c0-6 3-11 8-13-1 8-4 11-8 13Z"/><path d="M12 21C9 17 5 15 4 8c6 1 8 5 8 13Z"/>',
        default => '<rect x="3" y="8" width="18" height="11" rx="2"/><path d="M9 8V6.5A1.5 1.5 0 0 1 10.5 5h3A1.5 1.5 0 0 1 15 6.5V8"/><path d="M3 13h18"/>',
    };
@endphp

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
     stroke-linejoin="round" aria-hidden="true" {{ $attributes->class(['size-6']) }}>
    {!! $shapes !!}
</svg>
