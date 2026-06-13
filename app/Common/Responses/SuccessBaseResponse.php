<?php

namespace App\Common\Responses;

use Symfony\Component\HttpFoundation\Response;

readonly class SuccessBaseResponse
{
    public function __construct(
        public string $message,
        public mixed $data,
        public int $status = Response::HTTP_OK,
    ) {}

    public function toArray(): array
    {
        return [
            "success"   => true,
            "message"   => $this->message,
            "data"      => $this->data
        ];
    }
}
