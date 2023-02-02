<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\EntityManager\Selector\Definition;
use Medas\EntityManager\Selector\Selector;

class QuerySelector implements Selector
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
