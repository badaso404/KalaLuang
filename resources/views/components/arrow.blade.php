@props(['direction' => 'right'])

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
     stroke-linejoin="round" aria-hidden="true" {{ $attributes->class(['size-4']) }}>
    <path d="{{ $direction === 'left' ? 'M15 5l-7 7 7 7' : 'M9 5l7 7-7 7' }}" />
</svg>
