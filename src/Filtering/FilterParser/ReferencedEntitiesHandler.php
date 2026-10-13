<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\{Attributes\Service, Types\Collection};
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Relations\Join;
use Medas\RestRequestHandler\Exceptions\FilterOnUnrelatedEntity;

/**
 * A filter on a property of another entity ("subscriptions.tenant") needs that entity
 * joined, through the property of the filtered entity that refers to it.
 */
#[Service]
readonly class ReferencedEntitiesHandler
{
    public function __construct(
        private MetaDataManager $metaDataManager,
    )
    {
    }

    public function handle(Job $job): void
    {
        foreach (array_keys($job->referencedEntities) as $referencedEntity) {
            // A dotted filter naming the filtered entity's own store needs no join.
            if ($referencedEntity === $job->entity) {
                continue;
            }

            $job->elements[] = Join::c(
                $referencedEntity,
                $this->referringProperty($job->entity, $referencedEntity),
                $this->metaDataManager->get($referencedEntity)->idProperty->name,
            );
        }
    }

    /** @throws FilterOnUnrelatedEntity */
    private function referringProperty(string $entity, string $referencedEntity): string
    {
        $referringProperties = [];

        foreach ($this->metaDataManager->get($entity)->properties as $property) {
            if (!$property->type instanceof Collection && in_array($referencedEntity, $property->phpTypes, true)) {
                $referringProperties[] = $property->name;
            }
        }

        // With none there is nothing to join on; with several, which one is meant is a guess.
        if (count($referringProperties) !== 1) {
            throw new FilterOnUnrelatedEntity(
                $entity,
                $referencedEntity,
                count($referringProperties)
            );
        }

        return $referringProperties[0];
    }
}
