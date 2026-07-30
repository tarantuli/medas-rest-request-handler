<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\EntityManager\Selector\Selector;

class ExtraSelectorHasNoRequiredParameters extends BaseException
{
    public function __construct(Selector $selector)
    {
        parent::__construct($selector::class);
    }

    public function pattern(): string
    {
        return 'extra selector %s declares no required parameter to gate it on.';
    }
}
