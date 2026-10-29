@props(['status'])

{{--
    Indikator tiga tahap perjalanan satu lamaran: Dikirim → Ditinjau →
    Keputusan. Tahap terakhir berwarna hijau bila diterima dan merah bila
    ditolak, mengikuti alur status pada ApplicationStatus.
--}}
@php
    use App\Enums\ApplicationStatus;

    $reachedStep = match ($status) {
        ApplicationStatus::New => 1,
        ApplicationStatus::Reviewed => 2,
        ApplicationStatus::Accepted, ApplicationStatus::Rejected => 3,
    };

    $isRejected = $status === ApplicationStatus::Rejected;

    $steps = [
        1 => 'Dikirim',
        2 => 'Ditinjau',
        3 => $isRejected ? 'Ditolak' : ($status === ApplicationStatus::Accepted ? 'Diterima' : 'Keputusan'),
    ];
@endphp

<div {{ $attributes }}>
    <ol class="flex gap-1.5" aria-label="Tahap lamaran: {{ $steps[$reachedStep] }}">
        @foreach ($steps as $step => $label)
            <li @class([
                'h-1.5 flex-1 rounded-full',
                'bg-slate-200' => $step > $reachedStep,
                'bg-brand-600' => $step <= $reachedStep && ! ($step === 3 && $reachedStep === 3),
                'bg-rose-500' => $step === 3 && $reachedStep === 3 && $isRejected,
                'bg-emerald-500' => $step === 3 && $reachedStep === 3 && ! $isRejected,
            ])></li>
        @endforeach
    </ol>

    <div class="mt-2 flex justify-between text-xs">
        @foreach ($steps as $step => $label)
            <span @class([
                'font-semibold text-slate-700' => $step === $reachedStep,
                'text-slate-400' => $step !== $reachedStep,
            ])>{{ $label }}</span>
        @endforeach
    </div>
</div>
