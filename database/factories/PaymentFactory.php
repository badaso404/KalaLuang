<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\JobVacancy;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vacancy = JobVacancy::factory();

        return [
            'job_vacancy_id' => $vacancy,
            'company_id' => fn (array $attributes) => JobVacancy::find($attributes['job_vacancy_id'])->company_id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'amount' => config('kalaluang.listing_price'),
            'status' => PaymentStatus::Unpaid,
        ];
    }

    public function waitingConfirmation(): static
    {
        return $this->state([
            'status' => PaymentStatus::WaitingConfirmation,
            'proof_path' => 'payment-proofs/'.fake()->unique()->slug().'.jpg',
        ]);
    }

    public function paid(): static
    {
        return $this->state([
            'status' => PaymentStatus::Paid,
            'proof_path' => 'payment-proofs/'.fake()->unique()->slug().'.jpg',
            'paid_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => PaymentStatus::Rejected,
            'proof_path' => 'payment-proofs/'.fake()->unique()->slug().'.jpg',
        ]);
    }
}
