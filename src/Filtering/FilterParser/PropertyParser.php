<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\EntityClassFinder;
use Medas\EntityManager\Selector\Operants\Property;

/**
 * The property a filter or a sort names, the same check for both: one of the entity's
 * own, "name", or one of another entity, "products.name", which marks that entity as
 * referenced so ReferencedEntitiesHandler joins it in.
 *
 * Null when the name is no property of the entity it refers to, also when no entity has
 * the store it names. The caller then refuses the query part with CannotParseQueryValue
 * (400), so an unknown name never reaches the database as an unknown column.
 */
#[Service]
readonly class PropertyParser
{
    public function __construct(
        private EntityClassFinder $entityClassFinder,
    )
    {
    }

    public function parse(Job $job, string $name): Property|null
    {
        if (!str_contains($name, '.')) {
            return $this->propertyOf($job->entity, $name);
        }

        // Split at the first dot only, so "products.name.x" is refused rather than read
        // as "products.name"
        [$store, $name] = explode('.', $name, 2);
        $entity = $this->entityClassFinder->getByStore($store)?->className;

        if ($entity === null) {
            return null;
        }

        $property = $this->propertyOf($entity, $name);

        if ($property !== null) {
            $job->referencedEntities[$entity] = true;
        }

        return $property;
    }

    private function propertyOf(string $entity, string $name): Property|null
    {
        if ($entity !== '' && !property_exists($entity, $name)) {
            return null;
        }

        return Property::c($name, $entity);
    }
}
