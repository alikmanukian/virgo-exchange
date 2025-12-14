<?php

declare(strict_types=1);

namespace App\Actions\Trading;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Events\OrderbookUpdated;
use App\Exceptions\TradingException;
use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final readonly class CancelOrder
{
    /**
     * @throws AuthorizationException
     * @throws TradingException
     */
    public function handle(Order $order, User $user): Order
    {
        $order = DB::transaction(function () use ($order, $user): Order {
            /** @var Order $order */
            $order = Order::query()->lockForUpdate()->find($order->id);

            if ($order->user_id !== $user->id) {
                throw new AuthorizationException('You are not authorized to cancel this order.');
            }

            if ($order->status !== OrderStatus::Open) {
                throw TradingException::orderNotCancellable();
            }

            $order->update(['status' => OrderStatus::Cancelled]);

            if ($order->side === OrderSide::Buy) {
                /** @var User $user */
                $user = User::query()->lockForUpdate()->find($user->id);
                $user->increment('balance', $order->totalValue());
            } else {
                /** @var Asset $asset */
                $asset = Asset::query()
                    ->lockForUpdate()
                    ->where('user_id', $user->id)
                    ->where('symbol', $order->symbol)
                    ->first();
                $asset->decrement('locked_amount', $order->amount);
            }

            return $order->fresh();
        });

        OrderbookUpdated::dispatch($order->symbol);

        return $order;
    }
}
