<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption, Interfaces\Validator};

#[Service]
readonly class AllowedOrigins implements ConfigOption, Validator
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
        return 'allowed-origins';
    }

    public function description(): string
    {
        return 'A regular expression that matches the origins that are allowed to request resources';
    }

    public function isValid(mixed $value): bool
    {
        return $value === null || is_string($value);
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '/.+/';
    }
}
