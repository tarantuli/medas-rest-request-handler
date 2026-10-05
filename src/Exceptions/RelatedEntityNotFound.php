<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

/**
 * A request body refers to another entity by an id that does not exist. A bad
 * request rather than a 404: the addressed resource is fine, its content is not.
 */
class RelatedEntityNotFound extends BadRequest
{
    public function __construct(string $entity, mixed $id)
    {
        // The short class name, as in EntityNotFound.
        parent::__construct(substr(strrchr('\\' . $entity, '\\'), 1), (string) $id);
    }

    public function pattern(): string
    {
        return 'no %s found with id %s to refer to';
    }
}
