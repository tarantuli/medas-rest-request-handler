<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\PredefinedQueries;

use Medas\Core\{Attributes\Service, CachedImplementorList};

#[Service]
readonly class PredefinedQueryManager
{
    private CachedImplementorList $cachedImplementorList;

    public function __construct()
    {
        $this->cachedImplementorList = new CachedImplementorList(PredefinedQuery::class);
    }

    public function getByName(string $name): PredefinedQuery|null
    {
        return array_find(
            $this->cachedImplementorList->get(),
            fn($query) => $query->name() === $name
        );
    }
}
