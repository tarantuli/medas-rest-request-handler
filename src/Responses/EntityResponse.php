<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

class EntityResponse implements JsonResponse
{
    public function __construct(
        private readonly object $entity,
    )
    {
    }

    public function getJsonResponse(): array
    {
        return (array) $this->entity;
    }
}
