<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\{ConfigGroup, ConfigOption};
use Medas\ServiceManager\Interfaces\Validator;

#[Service]
class AllowedOrigins implements ConfigOption, Validator
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RestRequestHandlerGroup::instance();
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
