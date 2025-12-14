<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\AssetSymbol;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class OrderbookUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public AssetSymbol $symbol,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orderbook.'.$this->symbol->value),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderbookUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $symbol = mb_strtoupper($this->symbol->value);

        $bids = Order::query()
            ->open()
            ->forSymbol($symbol)
            ->buys()
            ->orderByDesc('price')
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->groupBy('price')
            ->map(fn (Collection $orders): array => [
                'price' => $orders->first()?->price,
                'amount' => $orders->sum('amount'),
                'total' => $orders->sum(fn (Order $order): string => bcmul($order->price, $order->amount, 8)),
                'count' => $orders->count(),
            ])
            ->values();

        $asks = Order::query()
            ->open()
            ->forSymbol($symbol)
            ->sells()
            ->orderBy('price')
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->groupBy('price')
            ->map(fn (Collection $orders): array => [
                'price' => $orders->first()?->price,
                'amount' => $orders->sum('amount'),
                'total' => $orders->sum(fn (Order $order): string => bcmul($order->price, $order->amount, 8)),
                'count' => $orders->count(),
            ])
            ->values();

        return [
            'symbol' => $symbol,
            'bids' => $bids,
            'asks' => $asks,
        ];
    }
}
