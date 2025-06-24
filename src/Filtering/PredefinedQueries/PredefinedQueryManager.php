<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\PredefinedQueries;

use Medas\Core\{Attributes\Service, Interfaces\CacheManager, Interfaces\ImplementorFinder};

#[Service]
readonly class PredefinedQueryManager
{
    public function __construct(
        private CacheManager      $cacheManager,
        private ImplementorFinder $implementorFinder,
    )
    {
    }

    public function getByName(string $name): PredefinedQuery|null
    {
        foreach ($this->getAll() as $query) {
            if ($query->name() === $name) {
                return $query;
            }
        }

        return null;
    }

    /** @return PredefinedQuery[] */
    public function getAll(): array
    {
        return $this->cacheManager->get()->get(
            __CLASS__,
            fn() => $this->implementorFinder->find(PredefinedQuery::class)
        );
    }
}
