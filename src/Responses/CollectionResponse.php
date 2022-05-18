<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

class CollectionResponse implements JsonResponse
{
    public function __construct(
        private readonly array $entities,
    )
    {
    }
    public function getJsonResponse(): array
    {
        return $this->entities;
    }
}
