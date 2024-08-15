<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{Exceptions\ClassIsNotAnEntity, MetaDataManager, Types\Relation};

#[Service]
readonly class SelectorBuilder
{
    public function __construct(
        private MetaDataManager $metaDataManager,
        private FilterParser    $filterParser,
    )
    {
    }

    public function build(string $entity, array $filters): QuerySelector
    {
        $selector = new QuerySelector($entity);
        $typeFinder = function ($name) use ($selector) {
            try {
                $metaData = $this->metaDataManager->get($selector->entity());
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

        $elements = $this->filterParser->parse($filters, $typeFinder);
        $definition = $selector->definition();

        foreach ($elements as $element) {
            $definition->add($element);
        }

        return $selector;
    }
}
