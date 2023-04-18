<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\Core\Attributes\Service;

#[Service]
class EntityResponseBuilder extends BaseResponseBuilder
{
    public function build(object $entity, object $controller): EntityResponse
    {
        return new EntityResponse($this->serialize($entity, $controller));
    }
}
