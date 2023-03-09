<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

class EntityResponse implements JsonResponse
{
    public function __construct(
        private readonly array $entityData,
    )
    {
    }

    public function getJsonResponse(): array
    {
        return $this->entityData;
    }
}
