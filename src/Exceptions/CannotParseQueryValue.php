<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

class CannotParseQueryValue extends BadRequest
{
    public function __construct(string $name, mixed $value)
    {
        parent::__construct($name, $value);
    }

    public function pattern(): string
    {
        return 'cannot parse query part %s with value %s';
    }
}
