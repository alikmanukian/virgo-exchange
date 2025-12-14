<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OrderSide;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => ['required', 'string', Rule::in(['BTC', 'ETH'])],
            'side' => ['required', 'string', Rule::enum(OrderSide::class)],
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symbol.in' => 'Symbol must be BTC or ETH.',
            'side.enum' => 'Side must be buy or sell.',
            'price.gt' => 'Price must be greater than 0.',
            'amount.gt' => 'Amount must be greater than 0.',
        ];
    }
}
