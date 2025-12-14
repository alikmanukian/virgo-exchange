<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trade>
 */
final class TradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(8, 1000, 100000);
        $amount = fake()->randomFloat(8, 0.001, 10);
        $total = bcmul((string) $price, (string) $amount, 8);
        $commission = bcmul($total, '0.015', 8);

        return [
            'buy_order_id' => Order::factory()->buy()->filled(),
            'sell_order_id' => Order::factory()->sell()->filled(),
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'symbol' => fake()->randomElement(['BTC', 'ETH']),
            'price' => $price,
            'amount' => $amount,
            'total' => $total,
            'commission' => $commission,
        ];
    }
}
