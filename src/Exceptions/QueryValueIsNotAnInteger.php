<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

class QueryValueIsNotAnInteger extends BadRequest
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function pattern(): string
    {
        return 'integer query value must be a whole number, "%s" given';
    }
}
