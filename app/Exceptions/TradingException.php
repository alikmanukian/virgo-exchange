<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class TradingException extends Exception
{
    public static function insufficientBalance(): self
    {
        return new self('Insufficient balance.');
    }

    public static function insufficientAssets(): self
    {
        return new self('Insufficient assets.');
    }

    public static function orderNotCancellable(): self
    {
        return new self('Order cannot be cancelled.');
    }
}
