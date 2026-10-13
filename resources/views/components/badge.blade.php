@props(['status' => null, 'classes' => 'bg-slate-100 text-slate-700'])

<span {{ $attributes->class(['badge', $status?->badgeClasses() ?? $classes]) }}>
    {{ $slot->isEmpty() ? $status?->label() : $slot }}
</span>
