<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

class QueryValueIsNotADate extends BadRequest
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function pattern(): string
    {
        return 'date query value must be an existing date formatted as Y-m-d, "%s" given';
    }
}
