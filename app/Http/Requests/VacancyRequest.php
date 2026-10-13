<?php

namespace App\Http\Requests;

use App\Enums\EducationLevel;
use App\Enums\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VacancyRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'is_remote' => ['boolean'],
            // `is_remote` is cast to a real boolean below, so compare against `false`.
            'location' => ['nullable', 'required_if:is_remote,false', 'string', 'max:100'],
            'min_education' => [
                'required',
                Rule::enum(EducationLevel::class)->only([
                    EducationLevel::D3,
                    EducationLevel::D4,
                    EducationLevel::S1,
                ]),
            ],
            'description' => ['required', 'string', 'max:5000'],
            'requirements' => ['required', 'string', 'max:5000'],
            'how_to_apply' => ['required', 'string', 'max:2000'],
            'salary_min' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'max:1000000000', 'gte:salary_min'],
            // BR-04: the company must declare the role accepts fresh graduates.
            'entry_level_declaration' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_remote' => $this->boolean('is_remote'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul lowongan',
            'category_id' => 'bidang',
            'employment_type' => 'jenis pekerjaan',
            'location' => 'lokasi',
            'min_education' => 'pendidikan minimal',
            'description' => 'deskripsi',
            'requirements' => 'persyaratan',
            'how_to_apply' => 'cara melamar',
            'salary_min' => 'gaji minimal',
            'salary_max' => 'gaji maksimal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location.required_if' => 'Lokasi wajib diisi kecuali lowongan ini remote.',
            'salary_max.gte' => 'Gaji maksimal tidak boleh lebih kecil dari gaji minimal.',
            'entry_level_declaration.accepted' => 'Anda harus menyatakan bahwa lowongan ini menerima lulusan baru tanpa pengalaman kerja.',
        ];
    }

    /**
     * The vacancy attributes, without the declaration checkbox.
     *
     * @return array<string, mixed>
     */
    public function vacancyAttributes(): array
    {
        return [
            ...$this->safe()->except('entry_level_declaration'),
            // BR-04 is enforced in storage, not just in the form.
            'requires_experience' => false,
        ];
    }
}
