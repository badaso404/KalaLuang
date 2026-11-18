@php
    $peak = max(1, $dailySeries->max('visits'));
@endphp

<x-admin-panel title="Statistik Pengunjung" heading="Pengunjung">
    <h2 class="mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">Ringkasan</h2>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat label="Kunjungan hari ini" :value="number_format($todayVisits, 0, ',', '.')"
                :hint="number_format($todayVisitors, 0, ',', '.').' pengunjung unik'" />
        <x-stat :label="'Kunjungan '.$windowDays.' hari'" :value="number_format($windowVisits, 0, ',', '.')"
                :hint="number_format($windowVisitors, 0, ',', '.').' pengunjung unik'" />
        <x-stat label="Rata-rata per hari"
                :value="number_format((int) round($windowVisits / $windowDays), 0, ',', '.')"
                :hint="'Selama '.$windowDays.' hari terakhir'" />
        <x-stat label="Kunjungan dari akun" :value="number_format($loggedInVisits, 0, ',', '.')"
                hint="Pengunjung yang sedang login" />
    </div>

    <h2 class="mt-8 mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">14 hari terakhir</h2>
    <div class="card p-4">
        @if ($dailySeries->sum('visits') === 0)
            <p class="py-6 text-center text-sm text-slate-600">Belum ada kunjungan yang tercatat.</p>
        @else
            <div class="flex h-48 items-end gap-1 sm:gap-2">
                @foreach ($dailySeries as $day)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-[10px] font-medium text-slate-500 sm:text-xs">{{ $day['visits'] }}</span>
                        <div class="w-full rounded-t bg-brand-500"
                             style="height: {{ max(2, (int) round($day['visits'] / $peak * 100)) }}%"
                             title="{{ $day['date']->translatedFormat('j F Y') }}: {{ $day['visits'] }} kunjungan, {{ $day['visitors'] }} pengunjung unik"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-1 sm:gap-2">
                @foreach ($dailySeries as $day)
                    <p class="flex-1 text-center text-[10px] text-slate-500 sm:text-xs">
                        {{ $day['date']->format('j/n') }}
                    </p>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div>
            <h2 class="mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">
                Halaman terpopuler
            </h2>
            @if ($topPages->isEmpty())
                <x-empty message="Belum ada halaman yang tercatat dikunjungi." />
            @else
                <div class="card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs tracking-wide text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 font-semibold">Halaman</th>
                                <th class="px-4 py-2 text-right font-semibold">Kunjungan</th>
                                <th class="px-4 py-2 text-right font-semibold">Unik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($topPages as $page)
                                <tr>
                                    <td class="max-w-0 truncate px-4 py-2 text-slate-700"
                                        title="{{ $page->path }}">{{ $page->path }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-slate-900">
                                        {{ number_format($page->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-slate-600">
                                        {{ number_format($page->visitors, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-6">
            <div>
                <h2 class="mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">
                    Sumber kunjungan
                </h2>
                <div class="card divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-4 py-2 text-sm">
                        <span class="text-slate-700">Langsung / tanpa rujukan</span>
                        <span class="font-medium text-slate-900">
                            {{ number_format($sources['direct'], 0, ',', '.') }}
                        </span>
                    </div>
                    @forelse ($sources['referrers'] as $referrer)
                        <div class="flex items-center justify-between gap-3 px-4 py-2 text-sm">
                            <span class="truncate text-slate-700">{{ $referrer->referrer_host }}</span>
                            <span class="font-medium text-slate-900">
                                {{ number_format($referrer->total, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="px-4 py-2 text-sm text-slate-500">
                            Belum ada kunjungan dari situs lain.
                        </p>
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">
                    Perangkat
                </h2>
                @if ($devices->isEmpty())
                    <x-empty message="Belum ada data perangkat." />
                @else
                    <div class="card divide-y divide-slate-100">
                        @foreach ($devices as $device)
                            <div class="flex items-center justify-between px-4 py-2 text-sm">
                                <span class="text-slate-700">{{ $device->device->label() }}</span>
                                <span class="font-medium text-slate-900">
                                    {{ number_format($device->total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <p class="mt-8 text-xs text-slate-500">
        Kunjungan dicatat tanpa menyimpan alamat IP: pengunjung unik dihitung dari sidik
        terenkripsi yang tidak bisa dikembalikan menjadi identitas. Kunjungan bot, panel
        admin, dan admin yang sedang login tidak ikut dihitung. Data lebih tua dari
        {{ $retentionDays }} hari dibuang otomatis.
    </p>
</x-admin-panel>
