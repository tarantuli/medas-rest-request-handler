<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class RouteDoesNotSpecifyEntity extends BaseException
{
    public function __construct(object $controller)
    {
        parent::__construct($controller::class);
    }

    public function pattern(): string
    {
        return 'route attribute on class %s does not specify endpointForEntity';
    }
}
