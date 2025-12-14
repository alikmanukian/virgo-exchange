<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AssetResource;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Repositories\TradingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TradingController extends Controller
{
    public function index(Request $request, TradingRepository $repository): Response
    {
        /** @var User $user */
        $user = $request->user();
        /** @var string $symbol */
        $symbol = mb_strtoupper($request->query('symbol', 'BTC'));

        return Inertia::render('Trading', [
            'balance' => fn () => $user->balance,
            'assets' => fn () => AssetResource::collection($repository->getAssets($user)),
            'orders' => fn () => OrderResource::collection($repository->getOrders($user)),
            'orderbook' => fn () => $repository->getOrderbook($symbol),
        ]);
    }
}
