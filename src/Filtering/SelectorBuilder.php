<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\{Attributes\Service, Types\Relation};
use Medas\EntityManager\{
    Exceptions\ClassIsNotAnEntity,
    Filters\OwnershipFilterApplier,
    MetaDataManager
};

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
        $typeFinder = function ($name, $metaData) {
            try {
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

        return $selector;
    }
}
