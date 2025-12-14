<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class TradingSeeder extends Seeder
{
    public function run(): void
    {
        $trader = User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Trader',
                'email' => 'trader@example.com',
                'password' => Hash::make('password'),
                'balance' => '100000.00000000',
            ]);

        Asset::factory()->btc()->withAmount('1.00000000')->create(['user_id' => $trader->id]);
        Asset::factory()->eth()->withAmount('10.00000000')->create(['user_id' => $trader->id]);

        $seller = User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Seller',
                'email' => 'seller@example.com',
                'password' => Hash::make('password'),
                'balance' => '50000.00000000',
            ]);

        Asset::factory()->btc()->withAmount('5.00000000')->create(['user_id' => $seller->id]);
        Asset::factory()->eth()->withAmount('50.00000000')->create(['user_id' => $seller->id]);
    }
}
