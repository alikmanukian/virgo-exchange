<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class TradingRepository
{
    /**
     * @return Collection<int, Asset>
     */
    public function getAssets(User $user): Collection
    {
        return $user->assets;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(User $user): Collection
    {
        return $user->orders()->latest()->limit(50)->get();
    }

    /**
     * @return array{
     *     symbol: string,
     *     bids: Collection<int, array{price: string, amount: string, total: string, count: int}>,
     *     asks: Collection<int, array{price: string, amount: string, total: string, count: int}>
     * }
     */
    public function getOrderbook(string $symbol, int $limit = 20): array
    {
        return [
            'symbol' => $symbol,
            'bids' => $this->getOrderbookSide($symbol, 'buy', $limit),
            'asks' => $this->getOrderbookSide($symbol, 'sell', $limit),
        ];
    }

    /**
     * @param  'buy'|'sell'  $side
     * @return Collection<int, array{price: string, amount: string, total: string, count: int}>
     */
    private function getOrderbookSide(string $symbol, string $side, int $limit): Collection
    {
        return Order::query()
            ->forSymbol($symbol)
            ->open()
            ->when($side === 'buy', fn (Builder $q): Builder => $q->buys()->orderByDesc('price'))
            ->when($side === 'sell', fn (Builder $q): Builder => $q->sells()->orderBy('price'))
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->groupBy('price')
            ->map(function (Collection $orders): array {
                /** @var Order $first */
                $first = $orders->first();
                /** @var numeric-string $amount */
                $amount = $orders->sum('amount');

                return [
                    'price' => $first->price,
                    'amount' => $amount,
                    'total' => $orders->sum(fn (Order $order): string => bcmul($order->price, $order->amount, 8)),
                    'count' => $orders->count(),
                ];
            })
            ->values();
    }
}
