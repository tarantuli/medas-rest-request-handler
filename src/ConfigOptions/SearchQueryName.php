<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class SearchQueryName implements ConfigOption
{
    public function __construct(
        private RestRequestHandlerGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'search-query-name';
    }

    public function description(): string
    {
        return 'The name of the query parameter that triggers a QuerySelector-based search on GetCollection routes';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'query';
    }
}
