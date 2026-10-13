<?php

namespace App\Http\Requests;

use App\Enums\EducationLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobSeekerProfileRequest extends FormRequest
{
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
        ];
    }
}
