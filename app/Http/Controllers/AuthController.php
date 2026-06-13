<?php

namespace App\Http\Controllers;

use App\Common\Responses\ErrorBaseResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(
                data: (new ErrorBaseResponse(
                    message: __('messages.auth.invalid_credentials'),
                    status: HttpResponse::HTTP_UNAUTHORIZED,
                ))->toArray(),
                status: HttpResponse::HTTP_UNAUTHORIZED,
                options: JSON_UNESCAPED_UNICODE,
            );
        }

        $user = Auth::user();

        $accessToken = $user->createToken(
            name: 'access-token',
            abilities: ['access'],
            expiresAt: now()->addMinutes(60),
        )->plainTextToken;

        $refreshToken = $user->createToken(
            name: 'refresh-token',
            abilities: ['refresh'],
            expiresAt: now()->addDays(30),
        )->plainTextToken;

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
        $user = $request->user();

        $user->tokens()->where('name', 'access-token')->delete();

        $accessToken = $user->createToken(
            name: 'access-token',
            abilities: ['access'],
            expiresAt: now()->addMinutes(60),
        )->plainTextToken;

        $refreshToken = $user->createToken(
            name: 'refresh-token',
            abilities: ['refresh'],
            expiresAt: now()->addDays(30),
        )->plainTextToken;

        $user->currentAccessToken()->delete();

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
        $request->user()->tokens()->delete();

        return $this->response(
            message: __('messages.auth.logout'),
            status: HttpResponse::HTTP_NO_CONTENT,
        );
    }
}
