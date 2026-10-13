<?php

namespace Database\Factories;

use App\Enums\CompanyVerificationStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory()->company(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'industry' => fake()->randomElement(['Teknologi', 'Manufaktur', 'Agensi Kreatif', 'Ritel']),
            'city' => fake()->randomElement(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta']),
            'address' => fake()->address(),
            'website' => 'https://'.fake()->unique()->domainName(),
            'description' => fake()->paragraph(),
            'verification_status' => CompanyVerificationStatus::Pending,
        ];
    }

    public function verified(): static
    {
        return $this->state([
            'verification_status' => CompanyVerificationStatus::Verified,
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'verification_status' => CompanyVerificationStatus::Rejected,
            'verification_note' => 'Dokumen tidak terbaca.',
        ]);
    }

    public function suspended(): static
    {
        return $this->state([
            'verification_status' => CompanyVerificationStatus::Suspended,
            'verification_note' => 'Melanggar ketentuan.',
        ]);
    }
}
