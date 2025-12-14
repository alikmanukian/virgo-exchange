<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\OrderResource;
use App\Http\Resources\TradeResource;
use App\Models\Trade;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderMatched implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Trade $trade,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->trade->buyer_id),
            new PrivateChannel('user.'.$this->trade->seller_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderMatched';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->trade->load(['buyOrder', 'sellOrder', 'buyer', 'seller']);

        return [
            'trade' => new TradeResource($this->trade),
            'buy_order' => new OrderResource($this->trade->buyOrder),
            'sell_order' => new OrderResource($this->trade->sellOrder),
            'buyer_balance' => $this->trade->buyer->balance,
            'seller_balance' => $this->trade->seller->balance,
        ];
    }
}
