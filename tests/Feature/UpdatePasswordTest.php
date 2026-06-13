<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can update their password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $accessToken = $user->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->putJson('/user/me/password', [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

    $response->assertNoContent();

    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

test('update password fails with wrong current password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $accessToken = $user->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->putJson('/user/me/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

    $response->assertUnprocessable();
});

test('update password fails when confirmation does not match', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $accessToken = $user->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->putJson('/user/me/password', [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertUnprocessable();
});

test('update password requires authentication', function () {
    $response = $this->putJson('/user/me/password', [
        'current_password' => 'old-password',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertUnauthorized();
});
