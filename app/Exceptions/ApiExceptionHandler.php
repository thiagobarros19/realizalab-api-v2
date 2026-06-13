<?php

namespace App\Exceptions;

use App\Common\Responses\ErrorBaseResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function render(Throwable $e): JsonResponse
    {
        self::log($e);

        return match (true) {
            $e instanceof ValidationException => self::validation($e),
            $e instanceof AuthenticationException => self::authentication(),
            $e instanceof AuthorizationException => self::authorization(),
            $e instanceof AccessDeniedHttpException => self::authorization(),
            $e instanceof ModelNotFoundException => self::modelNotFound($e),
            $e instanceof NotFoundHttpException => self::notFound(),
            $e instanceof MethodNotAllowedHttpException => self::methodNotAllowed(),
            $e instanceof TooManyRequestsHttpException => self::tooManyRequests(),
            default => self::generic(),
        };
    }

    private static function log(Throwable $e): void
    {
        $isClientError = $e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof AccessDeniedHttpException
            || $e instanceof NotFoundHttpException
            || $e instanceof ModelNotFoundException
            || $e instanceof MethodNotAllowedHttpException
            || $e instanceof TooManyRequestsHttpException;

        $context = [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($isClientError) {
            Log::warning($e->getMessage(), $context);
        } else {
            Log::error($e->getMessage(), [
                ...$context,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private static function response(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json(
            data: new ErrorBaseResponse(message: $message, status: $status, errors: $errors)->toArray(),
            status: $status,
            options: JSON_UNESCAPED_UNICODE,
        );
    }

    private static function validation(ValidationException $e): JsonResponse
    {
        return self::response(
            message: __('exceptions.validation_error'),
            status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
            errors: $e->errors(),
        );
    }

    private static function authentication(): JsonResponse
    {
        return self::response(
            message: __('exceptions.unauthenticated'),
            status: HttpResponse::HTTP_UNAUTHORIZED,
        );
    }

    private static function authorization(): JsonResponse
    {
        return self::response(
            message: __('exceptions.unauthorized'),
            status: HttpResponse::HTTP_FORBIDDEN,
        );
    }

    private static function modelNotFound(ModelNotFoundException $e): JsonResponse
    {
        $model = class_basename($e->getModel());
        $ids = implode(', ', $e->getIds());

        return self::response(
            message: __('exceptions.resource_not_found', ['model' => $model, 'ids' => $ids]),
            status: HttpResponse::HTTP_NOT_FOUND,
        );
    }

    private static function notFound(): JsonResponse
    {
        return self::response(
            message: __('exceptions.route_not_found', ['route' => request()->path()]),
            status: HttpResponse::HTTP_NOT_FOUND,
        );
    }

    private static function methodNotAllowed(): JsonResponse
    {
        return self::response(
            message: __('exceptions.method_not_allowed'),
            status: HttpResponse::HTTP_METHOD_NOT_ALLOWED,
        );
    }

    private static function tooManyRequests(): JsonResponse
    {
        return self::response(
            message: __('exceptions.too_many_requests'),
            status: HttpResponse::HTTP_TOO_MANY_REQUESTS,
        );
    }

    private static function generic(): JsonResponse
    {
        return self::response(
            message: __('exceptions.internal_error'),
            status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        );
    }
}
