<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaDataManager;
use Medas\RestRequestHandler\Exceptions\TwoClassesMapToSameEntityName;
use Medas\Routing\{EntityAlias, HandlerManager, Parameters\Constant, RouteHandler};

#[Service]
readonly class EndpointsParser
{
    public function __construct(
        private HandlerManager  $handlerManager,
        private MetaDataManager $metaDataManager,
        private TypeResolver    $typeResolver,
    )
    {
    }

    /**
     * Returns an array of EntityData objects, keyed by entity name.
     * @return EntityData[]
     */
    public function parse(): array
    {
        $entities = [];

        foreach ($this->handlerManager->getHandlers() as $handler) {
            $entityClass = $handler->route()->endpointForEntity();

            if ($entityClass === null) {
                continue;
            }

            $entityData = $this->findOrCreateEntityData($entities, $entityClass);

            $entityData->operations[] = $this->buildOperation($handler, $entityClass);
        }

        return $entities;
    }

    /** @param EntityData[] $entities */
    private function findOrCreateEntityData(array &$entities, string $entityClass): EntityData
    {
        $name = $this->resolveEntityName($entityClass);

        if (isset($entities[$name])) {
            if ($entities[$name]->entityClass !== $entityClass) {
                throw new TwoClassesMapToSameEntityName(
                    $entities[$name]->entityClass,
                    $entityClass,
                    $name
                );
            }

            return $entities[$name];
        }

        $entityData = new EntityData($entityClass);

        $entityData->metadata = $this->metaDataManager->get($entityClass);

        return $entities[$name] = $entityData;
    }

    private function resolveEntityName(string $entityClass): string
    {
        $reflection = new \ReflectionClass($entityClass);
        $aliases = $reflection->getAttributes(EntityAlias::class);

        if ($aliases !== []) {
            return $aliases[0]->newInstance()->alias;
        }

        return $reflection->getShortName();
    }

    private function buildOperation(RouteHandler $handler, string $entityClass): EndpointData
    {
        return new EndpointData(
            type: $handler->route()->endpointType ?? $this->typeResolver->resolve(
                $handler,
                $entityClass
            ),
            method: $handler->method()->name(),
            href: $this->buildHref($handler),
        );
    }

    private function buildHref(RouteHandler $handler): string
    {
        $segments = [];

        foreach ($handler->parameters() as $parameter) {
            $segments[] = $parameter instanceof Constant
                ? $parameter->readablePattern()
                : '{' . $parameter->name() . '}';
        }

        return '/' . implode('/', $segments);
    }
}
