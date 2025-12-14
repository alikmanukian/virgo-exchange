<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
final class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'symbol' => fake()->randomElement(['BTC', 'ETH']),
            'amount' => '0.00000000',
            'locked_amount' => '0.00000000',
        ];
    }

    public function withAmount(string $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $amount,
        ]);
    }

    public function forSymbol(string $symbol): static
    {
        return $this->state(fn (array $attributes): array => [
            'symbol' => $symbol,
        ]);
    }

    public function btc(): static
    {
        return $this->forSymbol('BTC');
    }

    public function eth(): static
    {
        return $this->forSymbol('ETH');
    }
}
