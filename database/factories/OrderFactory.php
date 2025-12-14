<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
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
            'side' => fake()->randomElement(OrderSide::cases()),
            'price' => fake()->randomFloat(8, 1000, 100000),
            'amount' => fake()->randomFloat(8, 0.001, 10),
            'status' => OrderStatus::Open,
        ];
    }

    public function buy(): static
    {
        return $this->state(fn (array $attributes): array => [
            'side' => OrderSide::Buy,
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes): array => [
            'side' => OrderSide::Sell,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Open,
        ]);
    }

    public function filled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Filled,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Cancelled,
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

    public function withPrice(string $price): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => $price,
        ]);
    }

    public function withAmount(string $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $amount,
        ]);
    }
}
