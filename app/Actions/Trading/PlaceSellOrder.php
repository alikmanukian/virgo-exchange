<?php

declare(strict_types=1);

namespace App\Actions\Trading;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Events\OrderbookUpdated;
use App\Exceptions\TradingException;
use App\Jobs\ProcessOrderMatching;
use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class PlaceSellOrder
{
    /**
     * @param  numeric-string  $price
     * @param  numeric-string  $amount
     *
     * @throws TradingException
     */
    public function handle(User $user, string $symbol, string $price, string $amount): Order
    {
        /** @var Order $order */
        $order = DB::transaction(function () use ($user, $symbol, $price, $amount): Order {
            $symbol = mb_strtoupper($symbol);

            /** @var Asset|null $asset */
            $asset = Asset::query()
                ->where('user_id', $user->id)
                ->where('symbol', $symbol)
                ->lockForUpdate()
                ->first();

            if (! $asset || bccomp($asset->availableAmount(), $amount, 8) < 0) {
                throw TradingException::insufficientAssets();
            }

            $asset->increment('locked_amount', (float) $amount);

            /** @var Order $order */
            $order = Order::query()->create([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'side' => OrderSide::Sell,
                'price' => $price,
                'amount' => $amount,
                'status' => OrderStatus::Open,
            ]);

            dispatch(new ProcessOrderMatching($order->id))->afterCommit();

            return $order;
        });

        event(new OrderbookUpdated($order->symbol));

        return $order;
    }
}
