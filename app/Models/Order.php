<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'side' => OrderSide::class,
            'status' => OrderStatus::class,
            'price' => 'decimal:8',
            'amount' => 'decimal:8',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function totalValue(): string
    {
        return bcmul($this->price, $this->amount, 8);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    #[Scope]
    public function open(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Open);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    #[Scope]
    public function forSymbol(Builder $query, string $symbol): Builder
    {
        return $query->where('symbol', $symbol);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    #[Scope]
    public function buys(Builder $query): Builder
    {
        return $query->where('side', OrderSide::Buy);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    #[Scope]
    public function sells(Builder $query): Builder
    {
        return $query->where('side', OrderSide::Sell);
    }
}
