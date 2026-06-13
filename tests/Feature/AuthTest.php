<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can login and receives access and refresh tokens', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->postJson('/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);

    expect($user->tokens()->count())->toBe(2);
});

test('login fails with invalid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->postJson('/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized();
});

test('user can refresh tokens using the refresh token', function () {
    $user = User::factory()->create();

    $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30));

    $response = $this->withToken($refreshToken->plainTextToken)
        ->postJson('/auth/refresh');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);

    expect($user->fresh()->tokens()->count())->toBe(2);
});

test('refresh endpoint rejects access tokens', function () {
    $user = User::factory()->create();

    $accessToken = $user->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->postJson('/auth/refresh');

    $response->assertForbidden();
});

test('user can logout and all tokens are revoked', function () {
    $user = User::factory()->create();

    $accessToken = $user->createToken('access-token', ['access'], now()->addMinutes(60));
    $user->createToken('refresh-token', ['refresh'], now()->addDays(30));

    $response = $this->withToken($accessToken->plainTextToken)
        ->postJson('/auth/logout');

    $response->assertNoContent();

    expect($user->fresh()->tokens()->count())->toBe(0);
});

test('logout endpoint rejects refresh tokens', function () {
    $user = User::factory()->create();

    $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30));

    $response = $this->withToken($refreshToken->plainTextToken)
        ->postJson('/auth/logout');

    $response->assertForbidden();
});

test('protected routes reject refresh tokens', function () {
    $user = User::factory()->create();

    $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30));

    $response = $this->withToken($refreshToken->plainTextToken)
        ->getJson('/user/me');

    $response->assertForbidden();
});
