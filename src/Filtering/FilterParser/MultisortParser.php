<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\EntityClassFinder;
use Medas\EntityManager\Selector\{Operants\Property, Sorting\SortBy, Sorting\SortDirection};
use Medas\RestRequestHandler\Exceptions\InvalidSortDirection;

#[Service]
class MultisortParser
{
    public function __construct(
        private readonly EntityClassFinder $entityClassFinder,
    )
    {
    }

    public function parse(Job $job, string $value): void
    {
        $parts = explode(',', $value);

        foreach ($parts as $part) {
            $direction = substr($part, 0, 1);

            if ($direction !== '<' && $direction !== '>') {
                throw new InvalidSortDirection($direction);
            }

            $job->elements[] = SortBy::c(
                $this->property($job, substr($part, 1)),
                $direction === '>' ? SortDirection::DESC : SortDirection::ASC
            );
        }
    }

    // A property of another entity, "<products.name", sorts by that entity's
    // property, the same way a dotted filter compares it: the entity is marked as
    // referenced, so ReferencedEntitiesHandler joins it in.
    private function property(Job $job, string $name): Property
    {
        if (!str_contains($name, '.')) {
            return Property::c($name);
        }

        [$store, $name] = explode('.', $name);
        $entity = $this->entityClassFinder->getByStore($store)->className;
        $job->referencedEntities[$entity] = true;

        return Property::c($name, $entity);
    }
}
