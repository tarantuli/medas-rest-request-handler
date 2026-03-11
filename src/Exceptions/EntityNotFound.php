<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class EntityNotFound extends BaseException
{
    public function __construct(string $entity, mixed $value)
    {
        parent::__construct($entity, $value);
    }

    public function pattern(): string
    {
        return 'entity of class %s not found with id "%s"';
    }
}
