<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Separate token guard for the patient portal: a token authenticated
        // here can only ever resolve to a Patient, never a staff User, and
        // vice versa staff tokens are rejected by this guard.
        Auth::viaRequest('patient', function (Request $request) {
            $token = $request->bearerToken();

            if (! $token) {
                return null;
            }

            $accessToken = PersonalAccessToken::findToken($token);

            if (! $accessToken || ! $accessToken->tokenable instanceof Patient) {
                return null;
            }

            if ($accessToken->expires_at?->isPast()) {
                return null;
            }

            $accessToken->forceFill(['last_used_at' => now()])->save();

            return $accessToken->tokenable->withAccessToken($accessToken);
        });
    }
}
