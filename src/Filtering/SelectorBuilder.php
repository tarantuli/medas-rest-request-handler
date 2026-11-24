<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\{Attributes\Service, Types\Boolean, Types\DateTime, Types\Relation};
use Medas\EntityManager\Exceptions\ClassIsNotAnEntity;
use Medas\EntityManager\Filters\OwnershipFilterApplier;
use Medas\EntityManager\Interfaces\HasSoftDeletes;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{
    Conditions\WhereIsNull,
    DerivedValues\DerivedValue,
    Operants\Property
};

#[Service]
readonly class SelectorBuilder
{
    public function __construct(
        private FilterParser                     $filterParser,
        private FilterParser\ComparisonExtractor $comparisonExtractor,
        private MetaDataManager                  $metaDataManager,
        private OwnershipFilterApplier           $ownershipFilterApplier,
    )
    {
    }

    /**
     * @param array<string, DerivedValue> $derivedValues names a filter or sort may name
     *                                                  beside the entity's properties
     */
    public function build(string $entity, array $filters, array $derivedValues = []): QuerySelector
    {
        $selector = new QuerySelector($entity);
        $metaData = $this->metaDataManager->get($entity);
        $filters = $this->ownershipFilterApplier->apply($metaData, $filters);
        $typeFinder = function (string $name, string $entityName) {
            try {
                $metaData = $this->metaDataManager->get($entityName);
                $type = $metaData->property($name)->type;

                if ($type instanceof Relation) {
                    return $this->metaDataManager->get($type->entity)->idProperty->type;
                }

                // The entity's own id needs its type as much as a relation's does: a
                // uuid given as text would otherwise never equal the stored bytes.
                if ($name === $metaData->idProperty?->name) {
                    return $type;
                }

                // Untyped, a boolean's "true" reaches the database as text, which compares
                // as 0: true, false and anything else then all selected the false rows.
                if ($type instanceof Boolean) {
                    return $type;
                }

                // Untyped, a date-time reached the database as the text sent, which the
                // database read in its own time zone - not PHP's, in which the value was
                // stored. Typed, it is parsed with its offset and stored-form serialized.
                if ($type instanceof DateTime) {
                    return $type;
                }
            }
            catch (ClassIsNotAnEntity) {
                // Do nothing
            }

            return null;
        };

        $elements = $this->filterParser->parse($entity, $filters, $typeFinder, $derivedValues);

        $selector->definition()->add(...$elements);

        // What the ownership filters cannot write as REST filters, they add as
        // conditions: still part of the query, so of its paging and count too.
        $selector->definition()->add(...$this->ownershipFilterApplier->conditions($metaData));

        // Soft-deleted rows are ordinary rows carrying a deletedAt timestamp;
        // hide them from collection reads, the standard default for a REST list.
        // A request that filters on deletedAt itself (e.g. deletedAt!=<null
        // sentinel> for a trash view) decides for itself, since the default would
        // contradict it.
        if (is_a($entity, HasSoftDeletes::class, true) && !$this->filtersOn($filters, 'deletedAt')) {
            $selector->definition()->add(WhereIsNull::c(Property::c('deletedAt')));
        }

        return $selector;
    }

    // Whether any filter compares the property, with whatever operator: the
    // operator suffix is stripped from the name the same way FilterParser does.
    /** @noinspection PhpSameParameterValueInspection */
    private function filtersOn(array $filters, string $property): bool
    {
        foreach (array_keys($filters) as $name) {
            $name = (string) $name;

            $this->comparisonExtractor->extract($name);

            if ($name === $property) {
                return true;
            }
        }

        return false;
    }
}
