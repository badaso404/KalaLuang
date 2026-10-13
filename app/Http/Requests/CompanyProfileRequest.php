<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyProfileRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company?->id;

        return [
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('companies', 'name')->ignore($companyId),
            ],
            'industry' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama perusahaan',
            'industry' => 'bidang industri',
            'city' => 'kota',
            'address' => 'alamat',
            'website' => 'situs/medsos',
            'description' => 'deskripsi',
            'logo' => 'logo',
        ];
    }
}
