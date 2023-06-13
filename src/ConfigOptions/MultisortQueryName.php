<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption, Validator};

#[Service]
class MultisortQueryName implements ConfigOption, Validator
{
    public function __construct(
        private readonly RestRequestHandlerGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'multisort-query-name';
    }

    public function description(): string
    {
        return 'The name of the multisort query parameter name, set to null to disable the filter completely';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'multisort';
    }

    public function isValid(mixed $value): bool
    {
        return $value === null || is_string($value);
    }
}
