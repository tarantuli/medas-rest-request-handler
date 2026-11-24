<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class IncludeQueryName implements ConfigOption
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
        return 'include-query-name';
    }

    public function description(): string
    {
        return 'The name of the query parameter through which a GetCollection request asks for the values of its CollectionIncludes';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'include';
    }
}
