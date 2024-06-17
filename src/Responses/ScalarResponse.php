<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

readonly class ScalarResponse implements JsonResponse
{
    public function __construct(
        private mixed $response,
    )
    {
    }

    public function getJsonResponse(): mixed
    {
        return $this->response;
    }
}
