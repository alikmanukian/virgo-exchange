<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Trading\MatchOrder;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcessOrderMatching implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $orderId,
    ) {}

    public function handle(MatchOrder $matchOrder): void
    {
        $order = Order::find($this->orderId);

        if ($order instanceof Order) {
            $matchOrder->handle($order);
        }

    }
}
