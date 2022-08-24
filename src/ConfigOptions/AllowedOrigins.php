<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\{ConfigGroup, ConfigOption};

#[Service]
class AllowedOrigins implements ConfigOption
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

    public function default(): string
    {
        return '/.+/';
    }
}
