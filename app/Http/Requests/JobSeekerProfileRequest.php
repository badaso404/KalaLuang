<?php

namespace App\Http\Requests;

use App\Enums\EducationLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobSeekerProfileRequest extends FormRequest
{
    /**
     * The skill catalogue cannot cover every trade, so the form also takes a
     * comma separated list of skills the job seeker typed themselves.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'custom_skills' => collect(explode(',', (string) $this->input('custom_skills')))
                ->map(fn (string $name): string => trim(preg_replace('/\s+/u', ' ', $name) ?? ''))
                ->filter()
                ->unique(fn (string $name): string => mb_strtolower($name))
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'education_level' => ['required', Rule::enum(EducationLevel::class)],
            'institution' => ['required', 'string', 'max:150'],
            'major' => ['required', 'string', 'max:150'],
            'graduation_year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['integer', 'exists:skills,id'],
            'custom_skills' => ['array', 'max:20'],
            'custom_skills.*' => ['string', 'min:2', 'max:60', 'regex:/^[\pL\pN][\pL\pN\s&().,\/+#\'-]*$/u'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'phone' => 'no. HP',
            'city' => 'kota domisili',
            'education_level' => 'jenjang pendidikan',
            'institution' => 'institusi pendidikan',
            'major' => 'jurusan',
            'graduation_year' => 'tahun lulus',
            'portfolio_url' => 'link portofolio',
            'cv' => 'CV',
            'custom_skills' => 'keahlian lainnya',
            'custom_skills.*' => 'keahlian lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cv.mimes' => 'CV harus berupa berkas PDF.',
            'cv.max' => 'Ukuran CV maksimal 2 MB.',
            'custom_skills.max' => 'Maksimal 20 keahlian tambahan sekaligus.',
            'custom_skills.*.min' => 'Setiap keahlian tambahan minimal 2 karakter.',
            'custom_skills.*.max' => 'Setiap keahlian tambahan maksimal 60 karakter.',
            'custom_skills.*.regex' => 'Keahlian tambahan hanya boleh berisi huruf, angka, dan tanda baca umum.',
        ];
    }
}
