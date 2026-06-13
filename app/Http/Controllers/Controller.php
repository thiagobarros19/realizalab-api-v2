<?php

namespace App\Http\Controllers;

use App\Common\Responses\SuccessBaseResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected function response(string $message = null, mixed $data = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            data: new SuccessBaseResponse(message: $message, data: $data, status: $status)->toArray(),
            status: $status ?? Response::HTTP_INTERNAL_SERVER_ERROR,
            options: JSON_UNESCAPED_UNICODE
        );
    }
}
