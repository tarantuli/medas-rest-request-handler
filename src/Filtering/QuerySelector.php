<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Interfaces\NotCacheable;
use Medas\EntityManager\Selector\{Definition, Selector};

readonly class QuerySelector implements Selector, NotCacheable
{
    private Definition $definition;

    public function __construct(
        private string $entity,
    )
    {
        $this->definition = new Definition($entity);
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function definition(): Definition
    {
        return $this->definition;
    }
}
