<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption, Validator};
use Medas\ServiceManager\AsSingleton;

class PerPageQueryName implements ConfigOption, Validator
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RestRequestHandlerGroup::instance();
    }

    public function name(): string
    {
        return 'per-page-query-name';
    }

    public function description(): string
    {
        return 'The name of the per page query parameter name, set to null to disable the filter completely';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'perPage';
    }

    public function isValid(mixed $value): bool
    {
        return $value === null || is_string($value);
    }
}
