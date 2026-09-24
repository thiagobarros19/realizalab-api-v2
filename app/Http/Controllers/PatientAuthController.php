<?php

namespace App\Http\Controllers;

use App\Common\Responses\ErrorBaseResponse;
use App\Http\Requests\PatientLoginRequest;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class PatientAuthController extends Controller
{
    public function login(PatientLoginRequest $request): JsonResponse
    {
        $document = $request->normalizedDocument();
        $throttleKey = 'patient-login:'.$document.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw new TooManyRequestsHttpException(RateLimiter::availableIn($throttleKey));
        }

        // Documents may be stored with or without punctuation, so compare
        // normalized digits in PHP rather than relying on DB-specific regex
        // functions (keeps this portable across MySQL/Postgres/SQLite).
        $patient = Patient::query()
            ->whereNotNull('document')
            ->get()
            ->first(fn (Patient $candidate) => preg_replace('/\D/', '', (string) $candidate->document) === $document);

        if (! $patient || ! $patient->birthday || $patient->birthday->toDateString() !== $request->input('birthday')) {
            RateLimiter::hit($throttleKey, 900);

            return response()->json(
                data: (new ErrorBaseResponse(
                    message: __('messages.auth.invalid_credentials'),
                    status: HttpResponse::HTTP_UNAUTHORIZED,
                ))->toArray(),
                status: HttpResponse::HTTP_UNAUTHORIZED,
                options: JSON_UNESCAPED_UNICODE,
            );
        }

        RateLimiter::clear($throttleKey);

        [$accessToken, $refreshToken] = $this->issueTokens($patient);

        return $this->response(
            message: __('messages.auth.login'),
            data: [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ],
        );
    }

    public function refresh(Request $request): JsonResponse
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');

        $patient->tokens()->where('name', 'patient-access-token')->delete();

        [$accessToken, $refreshToken] = $this->issueTokens($patient);

        $patient->currentAccessToken()->delete();

        return $this->response(
            message: __('messages.auth.refresh'),
            data: [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ],
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('patient')->tokens()->delete();

        return $this->response(
            message: __('messages.auth.logout'),
            status: HttpResponse::HTTP_NO_CONTENT,
        );
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');

        return $this->response(
            message: __('messages.patient.show'),
            data: [
                'id' => $patient->id,
                'name' => $patient->name,
            ],
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function issueTokens(Patient $patient): array
    {
        $accessToken = $patient->createToken(
            name: 'patient-access-token',
            abilities: ['patient-access'],
            expiresAt: now()->addMinutes(30),
        )->plainTextToken;

        $refreshToken = $patient->createToken(
            name: 'patient-refresh-token',
            abilities: ['patient-refresh'],
            expiresAt: now()->addDays(7),
        )->plainTextToken;

        return [$accessToken, $refreshToken];
    }
}
