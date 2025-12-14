<?php

declare(strict_types=1);

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('trading', absolute: false));
});

test('new users receive default balance and assets', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->balance)->toBe('100000.00000000');
    expect($user->assets)->toHaveCount(2);
    expect($user->assets->firstWhere('symbol', 'BTC')->amount)->toBe('1.00000000');
    expect($user->assets->firstWhere('symbol', 'ETH')->amount)->toBe('10.00000000');
});
