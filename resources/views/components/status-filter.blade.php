@props(['action', 'statuses', 'selected' => null, 'allLabel' => 'Semua status'])

<form method="GET" action="{{ $action }}" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
    <div class="min-w-48 flex-1">
        <label for="status" class="label">Status</label>
        <select id="status" name="status" class="input">
            <option value="">{{ $allLabel }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selected === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-primary">Filter</button>
    <a href="{{ $action }}" class="btn-secondary">Reset</a>
</form>
