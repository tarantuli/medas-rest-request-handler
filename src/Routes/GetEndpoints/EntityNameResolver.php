<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

use Medas\Core\Attributes\Service;
use Medas\Routing\EntityAlias;

#[Service]
readonly class EntityNameResolver
{
    public function resolve(string $entityClass): string
    {
        $reflection = new \ReflectionClass($entityClass);
        $aliases = $reflection->getAttributes(EntityAlias::class);

        if ($aliases !== []) {
            return $aliases[0]->newInstance()->alias;
        }

        return $reflection->getShortName();
    }
}
