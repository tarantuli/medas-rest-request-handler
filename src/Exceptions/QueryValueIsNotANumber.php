<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

class QueryValueIsNotANumber extends BadRequest
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function pattern(): string
    {
        return 'number query value must be numeric, "%s" given';
    }
}
