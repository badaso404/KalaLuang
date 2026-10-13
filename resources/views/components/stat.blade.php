@props(['label', 'value', 'hint' => null, 'href' => null])

<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif
    @class(['card p-4', 'transition hover:border-brand-200 hover:shadow-md' => $href !== null])>
    <p class="text-sm text-slate-600">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-0.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</{{ $href ? 'a' : 'div' }}>
