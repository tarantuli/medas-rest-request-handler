<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

class Job
{
    public int $page = 1;
    public int $pageSize;
    public array $elements = [];
    public array $referencedEntities = [];

    public function __construct(
        public string $entity,
    )
    {
    }
}
