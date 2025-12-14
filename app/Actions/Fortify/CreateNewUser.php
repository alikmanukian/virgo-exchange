<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\AssetSymbol;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            /** @var User $user */
            $user = User::query()->create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'balance' => '100000.00000000',
            ]);

            Asset::query()->create([
                'user_id' => $user->id,
                'symbol' => AssetSymbol::BTC,
                'amount' => '1.00000000',
                'locked_amount' => '0.00000000',
            ]);

            Asset::query()->create([
                'user_id' => $user->id,
                'symbol' => AssetSymbol::ETH,
                'amount' => '10.00000000',
                'locked_amount' => '0.00000000',
            ]);

            return $user;
        });
    }
}
