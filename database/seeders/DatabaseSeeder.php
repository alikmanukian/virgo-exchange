<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'balance' => '10000.00000000',
        ]);

        Asset::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'amount' => '1.00000000',
            'locked_amount' => '0.00000000',
        ]);

        Asset::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'ETH',
            'amount' => '10.00000000',
            'locked_amount' => '0.00000000',
        ]);
    }
}
