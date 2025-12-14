<?php

declare(strict_types=1);

namespace App\Actions\Trading;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Events\OrderbookUpdated;
use App\Exceptions\TradingException;
use App\Jobs\ProcessOrderMatching;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class PlaceBuyOrder
{
    /**
     * @throws TradingException
     */
    public function handle(User $user, string $symbol, string $price, string $amount): Order
    {
        $order = DB::transaction(function () use ($user, $symbol, $price, $amount): Order {
            /** @var User $user */
            $user = User::query()->lockForUpdate()->find($user->id);

            $totalCost = bcmul($price, $amount, 8);

            if (bccomp($user->balance, $totalCost, 8) < 0) {
                throw TradingException::insufficientBalance();
            }

            $user->decrement('balance', $totalCost);

            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => mb_strtoupper($symbol),
                'side' => OrderSide::Buy,
                'price' => $price,
                'amount' => $amount,
                'status' => OrderStatus::Open,
            ]);

            ProcessOrderMatching::dispatch($order)->afterCommit();

            return $order;
        });

        OrderbookUpdated::dispatch($order->symbol);

        return $order;
    }
}
