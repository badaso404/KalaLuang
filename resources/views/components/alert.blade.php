@if (session('status'))
    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold">Periksa kembali isian berikut:</p>
        <ul class="mt-1.5 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
