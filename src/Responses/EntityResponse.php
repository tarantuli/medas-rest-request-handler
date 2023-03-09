<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

class EntityResponse extends BaseEntityResponse implements JsonResponse
{
    public function __construct(
        private readonly object $entity,
        private readonly object $controller,
    )
    {
        parent::__construct($this->controller);
    }

    public function getJsonResponse(): array
    {
        return $this->normalizeEntity($this->entity);
    }
}
