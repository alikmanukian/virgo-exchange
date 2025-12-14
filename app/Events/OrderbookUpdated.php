<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderbookUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $symbol,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orderbook.'.$this->symbol),
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
        $symbol = mb_strtoupper($this->symbol);

        $bids = Order::query()
            ->open()
            ->forSymbol($symbol)
            ->buys()
            ->orderByDesc('price')
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->groupBy('price')
            ->map(fn ($orders) => [
                'price' => $orders->first()->price,
                'amount' => $orders->sum('amount'),
                'total' => $orders->sum(fn ($order) => bcmul((string) $order->price, (string) $order->amount, 8)),
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
            ->map(fn ($orders) => [
                'price' => $orders->first()->price,
                'amount' => $orders->sum('amount'),
                'total' => $orders->sum(fn ($order) => bcmul((string) $order->price, (string) $order->amount, 8)),
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
