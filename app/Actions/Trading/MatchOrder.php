<?php

declare(strict_types=1);

namespace App\Actions\Trading;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Events\OrderbookUpdated;
use App\Events\OrderMatched;
use App\Models\Asset;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class MatchOrder
{
    private const string COMMISSION_RATE = '0.015';

    public function handle(Order $order): ?Trade
    {
        /** @var Trade|null $trade */
        $trade = DB::transaction(function () use ($order): ?Trade {
            /** @var Order $order */
            $order = Order::query()->lockForUpdate()->find($order->id);

            if ($order->status !== OrderStatus::Open) {
                return null;
            }

            $matchingOrder = $this->findMatchingOrder($order);

            if (! $matchingOrder instanceof Order) {
                return null;
            }

            return $this->executeTrade($order, $matchingOrder);
        });

        if ($trade) {
            event(new OrderMatched($trade));
            event(new OrderbookUpdated($trade->symbol));
        }

        return $trade;
    }

    private function findMatchingOrder(Order $order): ?Order
    {
        $query = Order::query()
            ->lockForUpdate()
            ->where('symbol', $order->symbol)
            ->where('status', OrderStatus::Open)
            ->where('user_id', '!=', $order->user_id)
            ->where('amount', $order->amount);

        if ($order->side === OrderSide::Buy) {
            return $query
                ->where('side', OrderSide::Sell)
                ->where('price', '<=', $order->price)
                ->orderBy('price', 'asc')->oldest()
                ->first();
        }

        return $query
            ->where('side', OrderSide::Buy)
            ->where('price', '>=', $order->price)
            ->orderBy('price', 'desc')->oldest()
            ->first();
    }

    private function executeTrade(Order $incomingOrder, Order $matchingOrder): Trade
    {
        $buyOrder = $incomingOrder->side === OrderSide::Buy ? $incomingOrder : $matchingOrder;
        $sellOrder = $incomingOrder->side === OrderSide::Sell ? $incomingOrder : $matchingOrder;

        $tradePrice = $matchingOrder->price;
        $tradeAmount = $buyOrder->amount;
        $totalValue = bcmul($tradePrice, $tradeAmount, 8);

        $commission = bcmul($totalValue, self::COMMISSION_RATE, 8);

        $buyOrder->update(['status' => OrderStatus::Filled]);
        $sellOrder->update(['status' => OrderStatus::Filled]);

        /** @var User $buyer */
        $buyer = User::query()->lockForUpdate()->find($buyOrder->user_id);
        /** @var User $seller */
        $seller = User::query()->lockForUpdate()->find($sellOrder->user_id);

        /** @var Asset $sellerAsset */
        $sellerAsset = Asset::query()
            ->lockForUpdate()
            ->where('user_id', $seller->id)
            ->where('symbol', $buyOrder->symbol)
            ->first();

        $sellerAsset->decrement('locked_amount', (float) $tradeAmount);
        $sellerAsset->decrement('amount', (float) $tradeAmount);

        /** @var Asset $buyerAsset */
        $buyerAsset = Asset::query()
            ->lockForUpdate()
            ->firstOrCreate(
                ['user_id' => $buyer->id, 'symbol' => $buyOrder->symbol],
                ['amount' => '0.00000000', 'locked_amount' => '0.00000000']
            );
        $buyerAsset->increment('amount', (float) $tradeAmount);

        $seller->increment('balance', (float) $totalValue);

        $buyerPaid = bcmul($buyOrder->price, $tradeAmount, 8);
        $buyerOwes = bcadd($totalValue, $commission, 8);
        $refund = bcsub($buyerPaid, $buyerOwes, 8);

        $buyer->balance = bcadd($buyer->balance, $refund, 8);
        $buyer->save();

        /** @var Trade */
        return Trade::query()->create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'symbol' => $buyOrder->symbol,
            'price' => $tradePrice,
            'amount' => $tradeAmount,
            'total' => $totalValue,
            'commission' => $commission,
        ]);
    }
}
