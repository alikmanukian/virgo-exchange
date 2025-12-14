<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Trading\CancelOrder;
use App\Actions\Trading\PlaceBuyOrder;
use App\Actions\Trading\PlaceSellOrder;
use App\Enums\OrderSide;
use App\Exceptions\TradingException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function store(
        StoreOrderRequest $request,
        PlaceBuyOrder $placeBuyOrder,
        PlaceSellOrder $placeSellOrder,
    ): JsonResponse|RedirectResponse {
        /** @var array{symbol: string, side: string, price: numeric-string, amount: numeric-string} $validated */
        $validated = $request->validated();
        /** @var User $user */
        $user = $request->user();
        $side = OrderSide::from($validated['side']);
        $price = $validated['price'];
        $amount = $validated['amount'];

        try {
            $order = $side === OrderSide::Buy
                ? $placeBuyOrder->handle($user, $validated['symbol'], (string) $price, (string) $amount)
                : $placeSellOrder->handle($user, $validated['symbol'], (string) $price, (string) $amount);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Order placed successfully.',
                    'order' => new OrderResource($order),
                ], 201);
            }

            return back()->with('success', 'Order placed successfully.');
        } catch (TradingException $tradingException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $tradingException->getMessage()], 422);
            }

            return back()->withErrors(['order' => $tradingException->getMessage()]);
        }
    }

    public function destroy(Request $request, Order $order, CancelOrder $cancelOrder): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $cancelOrder->handle($order, $user);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Order cancelled successfully.']);
            }

            return back()->with('success', 'Order cancelled successfully.');
        } catch (AuthorizationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You are not authorized to cancel this order.'], 403);
            }

            return back()->withErrors(['order' => 'You are not authorized to cancel this order.']);
        } catch (TradingException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }
}
