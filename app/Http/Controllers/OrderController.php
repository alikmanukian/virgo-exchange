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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->when($request->query('symbol'), fn ($query, $symbol) => $query->forSymbol($symbol))
            ->when($request->query('side'), fn ($query, $side) => $query->where('side', $side))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function store(
        StoreOrderRequest $request,
        PlaceBuyOrder $placeBuyOrder,
        PlaceSellOrder $placeSellOrder,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();
        $side = OrderSide::from($validated['side']);
        $price = (string) $validated['price'];
        $amount = (string) $validated['amount'];

        try {
            $order = $side === OrderSide::Buy
                ? $placeBuyOrder->handle($user, $validated['symbol'], $price, $amount)
                : $placeSellOrder->handle($user, $validated['symbol'], $price, $amount);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Order placed successfully.',
                    'order' => new OrderResource($order),
                ], 201);
            }

            return back()->with('success', 'Order placed successfully.');
        } catch (TradingException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, Order $order, CancelOrder $cancelOrder): JsonResponse|RedirectResponse
    {
        try {
            $cancelOrder->handle($order, $request->user());

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
