<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\Validator
};

#[Service]
readonly class PredefinedQueryName implements ConfigOption, Validator
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
        return 'predefined-query-name';
    }

    public function description(): string
    {
        return 'The name of the predefined query parameter name, set to null to disable the filter completely';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'queryId';
    }

    public function isValid(mixed $value): bool
    {
        return $value === null || is_string($value);
    }
}
