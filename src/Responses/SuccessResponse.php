<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

readonly class SuccessResponse implements JsonResponse
{
    public function __construct(
        private bool $success,
    )
    {
    }

    public function getJsonResponse(): array
    {
        return [$this->success];
    }
}
