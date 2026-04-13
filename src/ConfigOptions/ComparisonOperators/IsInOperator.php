<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions\ComparisonOperators;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class IsInOperator implements ConfigOption
{
    public function __construct(
        private ComparisonOperatorGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'is-in';
    }

    public function description(): string
    {
        return 'The operator to use in queries to filter on "a ∈ {b, c, …}".
        
        For example, if this operator is "∈", then "entities?foo∈=bar,baz" would return entities with foo equal to "bar" or "baz". Set to null to disable this operator';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '∈';
    }
}
