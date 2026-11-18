<?php

namespace Database\Factories;

use App\Enums\DeviceType;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => '/'.fake()->randomElement(['', 'lowongan', 'lowongan/analis-data', 'masuk']),
            'route_name' => fake()->randomElement(['home', 'vacancies.index', 'login']),
            'visitor_hash' => hash('sha256', Str::random(16)),
            'device' => fake()->randomElement(DeviceType::cases()),
            'referrer_host' => fake()->boolean(30) ? fake()->domainName() : null,
            'user_id' => null,
            'created_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * Several visits that share one visitor, for unique-visitor assertions.
     */
    public function fromVisitor(string $visitorHash): static
    {
        return $this->state(fn (): array => ['visitor_hash' => $visitorHash]);
    }

    public function on(string $path): static
    {
        return $this->state(fn (): array => ['path' => $path]);
    }

    public function today(): static
    {
        return $this->state(fn (): array => ['created_at' => now()]);
    }
}
