<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\EntityManager\Selector\{Definition, Selector};
use Medas\ServiceManager\Cache\Interfaces\NotCacheable;

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
