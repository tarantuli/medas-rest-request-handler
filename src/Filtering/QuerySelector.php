<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\EntityManager\Selector\Definition;
use Medas\EntityManager\Selector\Selector;

class QuerySelector implements Selector
{
    private Definition $definition;

    public function __construct(private string $entity)
    {
        $this->definition = new Definition($entity);
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function get(): Definition
    {
        return $this->definition;
    }
}
