@props(['tabs'])

{{--
    Baris tab penyaring. Setiap tab berisi label, tautan, jumlah, dan penanda
    aktif: ['label' => ..., 'href' => ..., 'count' => ..., 'active' => bool].
--}}
<div class="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
     role="tablist">
    @foreach ($tabs as $tab)
        <a href="{{ $tab['href'] }}" role="tab" aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
           @class([
               'flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
               'bg-brand-600 text-white' => $tab['active'],
               'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => ! $tab['active'],
           ])>
            {{ $tab['label'] }}
            <span @class([
                'badge',
                'bg-white/20 text-white' => $tab['active'],
                'bg-slate-100 text-slate-600' => ! $tab['active'],
            ])>{{ $tab['count'] }}</span>
        </a>
    @endforeach
</div>
