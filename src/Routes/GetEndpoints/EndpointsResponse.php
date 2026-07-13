<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

use Medas\Core\{Interfaces\Type, Types\Collection, Types\Relation};
use Medas\EntityManager\MetaData;
use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

readonly class EndpointsResponse implements JsonResponse
{
    /** @var EntityData[] $entities */
    public function __construct(
        private array              $entities,
        private EntityNameResolver $entityNameResolver,
    )
    {
    }

    public function getJsonResponse(): array
    {
        return array_map(function ($entity) {
            return [
                'metaData' => $this->filterMetaData($entity->metadata),
                'operations' => $entity->operations,
            ];
        }, $this->entities);
    }

    private function filterMetaData(MetaData $metadata): array
    {
        $filtered = [
            'properties' => [],
        ];

        foreach ($metadata->properties as $property) {
            $filtered['properties'][$property->name] = [
                'type' => new \ReflectionClass($property->type)->getShortName(),
                'typeSpecifications' => $this->getTypeSpecifications($property->type),
                'hasDefault' => $property->hasDefault,
                'default' => $property->default,
                'isId' => $property->isId,
                'isGeneratedValue' => $property->isGeneratedValue,
                'isCreationTimestamp' => $property->isCreationTimestamp,
                'isModificationTimestamp' => $property->isModificationTimestamp,
                'isNullable' => $property->isNullable,
                'isUnique' => $property->isUnique,
            ];
        }

        return $filtered;
    }

    private function getTypeSpecifications(Type $type): array
    {
        if ($type instanceof Relation) {
            return [
                'entity' => $this->entityNameResolver->resolve($type->entity),
            ];
        }

        if ($type instanceof Collection) {
            return [
                'collectionType' => $this->entityNameResolver->resolve($type->collectionType),
                'contentType' => $this->entityNameResolver->resolve($type->contentType),
            ];
        }

        return array_filter(get_object_vars($type));
    }
}
