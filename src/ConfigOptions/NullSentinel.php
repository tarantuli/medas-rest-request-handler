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
readonly class NullSentinel implements ConfigOption, Validator
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
        return 'null-sentinel';
    }

    public function description(): string
    {
        return 'The query value read as "is null" (with the equality operator) or '
            . '"is not null" (with the not-equal operator), set to null to disable null filtering';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '∅';
    }

    public function isValid(mixed $value): bool
    {
        return $value === null || is_string($value);
    }
}
