<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption, Validator};

#[Service]
class DefaultPageSize implements ConfigOption, Validator
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
        return 'default-page-size';
    }

    public function description(): string
    {
        return 'The default size of pages when paginating';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 30;
    }

    public function isValid(mixed $value): bool
    {
        return is_int($value);
    }
}
