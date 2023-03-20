<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\ServiceManager\Attributes\Service;

#[Service]
class CollectionResponseBuilder extends BaseResponseBuilder
{
    /** @param object[] $entities */
    public function build(array $entities, object $controller): CollectionResponse
    {
        return new CollectionResponse(
            array_map(fn($entity) => $this->serialize($entity, $controller), $entities)
        );
    }
}
