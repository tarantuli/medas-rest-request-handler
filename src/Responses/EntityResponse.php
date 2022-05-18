<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;
use Medas\RestRequestHandler\Interfaces\Normalizer;

class EntityResponse implements JsonResponse
{
    public function __construct(
        private readonly object $entity,
        private readonly object $controller,
    )
    {
    }

    public function getJsonResponse(): array
    {
        return $this->controller instanceof Normalizer
            ? $this->controller->normalize($this->entity)
            : (array) $this->entity;
    }
}
