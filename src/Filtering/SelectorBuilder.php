<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\{Attributes\Service, Types\Relation};
use Medas\EntityManager\{Exceptions\ClassIsNotAnEntity, MetaDataManager};

#[Service]
readonly class SelectorBuilder
{
    public function __construct(
        private FilterParser    $filterParser,
        private MetaDataManager $metaDataManager,
    )
    {
    }

    public function build(string $entity, array $filters): QuerySelector
    {
        $selector = new QuerySelector($entity);
        $typeFinder = function ($name, $entity) {
            try {
                $metaData = $this->metaDataManager->get($entity);
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
