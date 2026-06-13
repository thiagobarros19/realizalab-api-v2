<?php

namespace App\Common\Responses;

use Symfony\Component\HttpFoundation\Response;

readonly class ErrorBaseResponse
{
    public function __construct(
        public string $message,
        public int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        public array $errors = [],
    ) {}

    public function toArray(): array
    {
        return [
            "success"   => false,
            "message"   => $this->message,
            "errors"    => $this->errors
        ];
    }
}
