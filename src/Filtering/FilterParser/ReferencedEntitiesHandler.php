<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaDataManager;

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
        foreach ($job->referencedEntities as $referencedEntity => $dump) {
            $metaData = $this->metaDataManager->get($referencedEntity);
        }
    }
}
