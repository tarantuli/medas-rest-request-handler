<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\{Attributes\Service, Types\Relation};
use Medas\EntityManager\Exceptions\ClassIsNotAnEntity;
use Medas\EntityManager\Filters\OwnershipFilterApplier;
use Medas\EntityManager\Interfaces\HasSoftDeletes;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Conditions\WhereIsNull, Operants\Property};

#[Service]
readonly class SelectorBuilder
{
    public function __construct(
        private FilterParser           $filterParser,
        private MetaDataManager        $metaDataManager,
        private OwnershipFilterApplier $ownershipFilterApplier,
    )
    {
    }

    public function build(string $entity, array $filters): QuerySelector
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
            }
            catch (ClassIsNotAnEntity) {
                // Do nothing
            }

            return null;
        };

        $elements = $this->filterParser->parse($entity, $filters, $typeFinder);

        $selector->definition()->add(...$elements);

        // Soft-deleted rows are ordinary rows carrying a deletedAt timestamp;
        // hide them from collection reads, the standard default for a REST list.
        if (is_a($entity, HasSoftDeletes::class, true)) {
            $selector->definition()->add(WhereIsNull::c(Property::c('deletedAt')));
        }

        return $selector;
    }
}
