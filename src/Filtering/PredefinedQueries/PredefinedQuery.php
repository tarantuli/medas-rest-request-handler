<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\PredefinedQueries;

use Medas\EntityManager\Selector\Element;

interface PredefinedQuery
{
    public function name(): string;

    /** @return Element[] */
    public function elements(): array;
}
