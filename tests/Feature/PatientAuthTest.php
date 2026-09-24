<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('patient can login with cpf and birthdate', function () {
    $patient = Patient::factory()->create([
        'document' => '529.982.247-25',
        'birthday' => '1990-05-10',
    ]);

    $response = $this->postJson('/patient/auth/login', [
        'document' => '52998224725',
        'birthday' => '1990-05-10',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);

    expect($patient->tokens()->count())->toBe(2);
});

test('patient login fails with invalid cpf checksum', function () {
    $response = $this->postJson('/patient/auth/login', [
        'document' => '11111111111',
        'birthday' => '1990-05-10',
    ]);

    $response->assertUnprocessable();
});

test('patient login fails with wrong birthdate', function () {
    Patient::factory()->create([
        'document' => '529.982.247-25',
        'birthday' => '1990-05-10',
    ]);

    $response = $this->postJson('/patient/auth/login', [
        'document' => '52998224725',
        'birthday' => '1991-01-01',
    ]);

    $response->assertUnauthorized();
});

test('patient login locks out after too many failed attempts', function () {
    Patient::factory()->create([
        'document' => '529.982.247-25',
        'birthday' => '1990-05-10',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/patient/auth/login', [
            'document' => '52998224725',
            'birthday' => '1991-01-01',
        ]);
    }

    $response = $this->postJson('/patient/auth/login', [
        'document' => '52998224725',
        'birthday' => '1990-05-10',
    ]);

    $response->assertStatus(429);
});

test('patient can refresh tokens', function () {
    $patient = Patient::factory()->create();

    $refreshToken = $patient->createToken('patient-refresh-token', ['patient-refresh'], now()->addDays(7));

    $response = $this->withToken($refreshToken->plainTextToken)
        ->postJson('/patient/auth/refresh');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);
});

test('patient can logout and all tokens are revoked', function () {
    $patient = Patient::factory()->create();

    $accessToken = $patient->createToken('patient-access-token', ['patient-access'], now()->addMinutes(30));

    $response = $this->withToken($accessToken->plainTextToken)
        ->postJson('/patient/auth/logout');

    $response->assertNoContent();

    expect($patient->fresh()->tokens()->count())->toBe(0);
});

test('staff token cannot access patient routes', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $accessToken = $user->createToken('access-token', ['access'], now()->addMinutes(60));

    $response = $this->withToken($accessToken->plainTextToken)
        ->getJson('/patient/me');

    $response->assertUnauthorized();
});

test('patient token cannot access staff routes', function () {
    $patient = Patient::factory()->create();
    $accessToken = $patient->createToken('patient-access-token', ['patient-access'], now()->addMinutes(30));

    $response = $this->withToken($accessToken->plainTextToken)
        ->getJson('/user/me');

    // Sanctum's token guard is polymorphic (resolves whatever model the
    // token belongs to), so isolation here comes from the distinct
    // `patient-access` vs `access` abilities, not a different guard
    // rejecting the token outright — hence 403, not 401.
    $response->assertForbidden();
});
