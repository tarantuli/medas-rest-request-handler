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
readonly class EnableEndpoints implements ConfigOption, Validator
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
        return 'enable-endpoints';
    }

    public function description(): string
    {
        return 'Whether the /endpoints discovery catalog (entities, their metadata, and their REST operations) is exposed';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): bool
    {
        return false;
    }

    public function isValid(mixed $value): bool
    {
        return is_bool($value);
    }
}
