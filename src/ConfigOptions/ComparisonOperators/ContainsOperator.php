<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions\ComparisonOperators;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class ContainsOperator implements ConfigOption
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
        return 'contains';
    }

    public function description(): string
    {
        return 'The operator to use in queries to filter on "a contains b".
        
        For example, if this operator is "*", then "entities?foo*=bar" would return entities with foo containing "bar". Set to null to disable this operator';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '*';
    }
}
