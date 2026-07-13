<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class CannotDetermineEndpointType extends BaseException
{
    public function __construct(
        string $handlerClass,
        string $entityClass,
        string $httpMethod,
    )
    {
        parent::__construct($handlerClass, $entityClass, $httpMethod);
    }

    public function pattern(): string
    {
        return '%s (bound to entity %s) has an unrecognized %s route shape - set endpointType explicitly on its Route attribute';
    }
}
