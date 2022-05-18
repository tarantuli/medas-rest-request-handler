<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;
use Medas\RestRequestHandler\Interfaces\Normalizer;

class CollectionResponse implements JsonResponse
{
    public function __construct(
        private readonly array  $entities,
        private readonly object $controller,
    )
    {
    }

    public function getJsonResponse(): array
    {
        $data = [];

        foreach ($this->entities as $entity) {
            $data[] = $this->controller instanceof Normalizer
                ? $this->controller->normalize($entity)
                : (array) $entity;
        }

        return $data;
    }
}
