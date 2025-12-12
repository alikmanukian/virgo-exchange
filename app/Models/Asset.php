<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'amount',
        'locked_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'locked_amount' => 'decimal:8',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availableAmount(): string
    {
        return bcsub($this->amount, $this->locked_amount, 8);
    }

    /**
     * @param  Builder<Asset>  $query
     * @return Builder<Asset>
     */
    #[Scope]
    public function forSymbol(Builder $query, string $symbol): Builder
    {
        return $query->where('symbol', $symbol);
    }
}
