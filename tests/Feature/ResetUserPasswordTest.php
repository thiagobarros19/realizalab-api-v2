<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can reset another user password by id', function () {
    $admin = User::factory()->create();
    $target = User::factory()->create(['password' => Hash::make('old-password')]);
    $accessToken = $admin->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->putJson("/user/{$target->id}/password", [
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

    $response->assertNoContent();

    expect(Hash::check('new-password123', $target->fresh()->password))->toBeTrue();
});

test('reset password fails when confirmation does not match', function () {
    $admin = User::factory()->create();
    $target = User::factory()->create();
    $accessToken = $admin->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->putJson("/user/{$target->id}/password", [
            'password' => 'new-password123',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertUnprocessable();
});

test('reset password returns 404 for non-existent user', function () {
    $admin = User::factory()->create();
    $accessToken = $admin->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->putJson('/user/non-existent-id/password', [
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

    $response->assertNotFound();
});

test('reset password requires authentication', function () {
    $target = User::factory()->create();

    $response = $this->putJson("/user/{$target->id}/password", [
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertUnauthorized();
});
