<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidDateTimeFormat extends BaseException
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function pattern(): string
    {
        return 'date-time value must be formatted as RFC 3339 with milliseconds (Y-m-d\TH:i:s.vP), "%s" given';
    }
}
