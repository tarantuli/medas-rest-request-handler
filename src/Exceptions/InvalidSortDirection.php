<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidSortDirection extends BaseException
{
    public function __construct(private readonly string $direction
    )
    {
        parent::__construct($this->direction);
    }

    public function pattern(): string
    {
        return 'invalid sort direction, found "%s" instead of "<" or ">"';
    }
}
