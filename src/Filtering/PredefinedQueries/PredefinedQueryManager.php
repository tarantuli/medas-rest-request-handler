<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\PredefinedQueries;

use Medas\Core\{Attributes\Service, Interfaces\ImplementorFinder};

#[Service]
readonly class PredefinedQueryManager
{
    public function __construct(
        private ImplementorFinder $implementorFinder,
    )
    {
    }

    public function getByName(string $name): PredefinedQuery|null
    {
        return array_find($this->getAll(), fn($query) => $query->name() === $name);
    }

    /** @return PredefinedQuery[] */
    public function getAll(): array
    {
        return cache(__CLASS__, fn() => $this->implementorFinder->find(PredefinedQuery::class));
    }
}
