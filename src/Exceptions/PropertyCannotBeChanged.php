<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

/**
 * An update tries to give a property a new value, but the property cannot be
 * changed after the entity was created (or cannot be written at all). Refused for
 * the whole request rather than skipped, so the client learns that its change did
 * not happen. Resending the stored value is not a change, and is accepted.
 */
class PropertyCannotBeChanged extends BadRequest
{
    public function __construct(string $entity, string $field)
    {
        // The short class name, as in EntityNotFound.
        parent::__construct(substr(strrchr('\\' . $entity, '\\'), 1), $field);
    }

    public function pattern(): string
    {
        return 'the %s field %s cannot be changed';
    }
}
