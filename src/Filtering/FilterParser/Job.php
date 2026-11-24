<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\EntityManager\Selector\DerivedValues\DerivedValue;

class Job
{
    public int $page = 1;
    public int $pageSize;
    public array $elements = [];
    public array $referencedEntities = [];

    /**
     * @param array<string, DerivedValue> $derivedValues the names a filter or sort may name
     *                                                  beside the entity's properties
     */
    public function __construct(
        public string $entity,
        public array  $derivedValues = [],
    )
    {
    }
}
