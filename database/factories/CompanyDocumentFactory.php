<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyDocument>
 */
class CompanyDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => DocumentType::Nib,
            'file_path' => 'company-documents/'.fake()->unique()->slug().'.pdf',
            'uploaded_at' => now(),
        ];
    }
}
