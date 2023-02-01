<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\ConfigOptions\ConfigGroup;
use Medas\ServiceManager\ConfigOptions\ConfigOption;
use Medas\ServiceManager\Values\Interfaces\Validator;

class MultisortQueryName implements ConfigOption, Validator
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RestRequestHandlerGroup::instance();
    }

    public function name(): string
    {
        return 'multisort-query-name';
    }

    public function description(): string
    {
        return 'The name of the multisort query parameter name, null to disable the filter';
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
