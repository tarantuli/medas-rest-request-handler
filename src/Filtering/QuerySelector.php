<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Interfaces\NotCacheable;
use Medas\EntityManager\Selector\{Definition, Selector};

class QuerySelector implements Selector, NotCacheable
{
    private Definition $definition;

    public function __construct(string $entity)
    {
        $this->definition = new Definition($entity);
    }

    public function definition(): Definition
    {
        return $this->definition;
    }
}
