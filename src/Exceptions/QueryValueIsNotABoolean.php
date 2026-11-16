<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

class QueryValueIsNotABoolean extends BadRequest
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function pattern(): string
    {
        return 'boolean query value must be true, false, 1 or 0, "%s" given';
    }
}
