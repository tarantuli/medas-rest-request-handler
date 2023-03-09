<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

class CollectionResponse extends BaseEntityResponse implements JsonResponse
{
    public function __construct(
        private readonly array  $entities,
        private readonly object $controller,
    )
    {
        parent::__construct($this->controller);
    }

    public function getJsonResponse(): array
    {
        $data = [];

        foreach ($this->entities as $entity) {
            $data[] = $this->normalizeEntity($entity);
        }

        return $data;
    }
}
