<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption, Validator};
use Medas\ServiceManager\AsSingleton;

class PageQueryName implements ConfigOption, Validator
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RestRequestHandlerGroup::instance();
    }

    public function name(): string
    {
        return 'page-query-name';
    }

    public function description(): string
    {
        return 'The name of the page query parameter name, set to null to disable the filter completely';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'page';
    }

    public function isValid(mixed $value): bool
    {
        return $value === null || is_string($value);
    }
}
