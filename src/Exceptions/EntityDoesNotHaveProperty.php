<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class EntityDoesNotHaveProperty extends BaseException
{
    public function __construct(string $className, string $propertyName)
    {
        parent::__construct(new \ReflectionClass($className)->getShortName(), $propertyName);
    }

    public function pattern(): string
    {
        return 'Entity %s does not have a property named "%s"';
    }
}
