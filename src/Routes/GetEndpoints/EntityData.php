<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

use Medas\EntityManager\MetaData;

class EntityData
{
    public MetaData $metadata;

    /** @var EndpointData[] */
    public array $operations = [];

    public function __construct(
        public string $entityClass,
    )
    {
    }
}
