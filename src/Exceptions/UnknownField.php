<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

/**
 * A request body holds a field the entity does not know: not one of its readable or
 * writable fields, and not one the controller handles itself. Refused for the whole
 * request rather than ignored, so a client sending a removed or misspelt field learns
 * that nothing of it was stored.
 */
class UnknownField extends BadRequest
{
    public function __construct(string $entity, string $field)
    {
        // The short class name, as in PropertyCannotBeChanged.
        parent::__construct(substr(strrchr('\\' . $entity, '\\'), 1), $field);
    }

    public function pattern(): string
    {
        return 'the %s has no field %s';
    }
}
