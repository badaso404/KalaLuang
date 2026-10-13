@props(['message'])

<div class="card px-4 py-10 text-center">
    <p class="text-sm text-slate-600">{{ $message }}</p>
    @if (! $slot->isEmpty())
        <div class="mt-4 flex justify-center">{{ $slot }}</div>
    @endif
</div>
