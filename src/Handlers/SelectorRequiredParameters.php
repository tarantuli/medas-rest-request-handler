<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Handlers;

use Medas\Core\{Attributes\Service, Interfaces\CacheManager};
use Medas\EntityManager\Selector\Selector;

#[Service]
readonly class SelectorRequiredParameters
{
    public function __construct(
        private CacheManager $cacheManager,
    )
    {
    }

    public function find(Selector $selector): array
    {
        return $this->cacheManager->get()->get(
            [self::class, $selector::class],
            fn() => $this->determine($selector)
        );
    }

    private function determine(Selector $selector): array
    {
        return array_map(
            fn($parameter) => $parameter->name,
            array_filter(
                $selector->definition()->parameters,
                fn($parameter) => !$parameter->hasDefault
            ),
        );
    }
}
