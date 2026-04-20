<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions\ClassGenerators;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class CreateCountController implements ConfigOption
{
    public function __construct(
        private ClassGeneratorGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'create-count-controller';
    }

    public function description(): string
    {
        return 'Whether to create a get collection count controller when creating all controllers';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): false
    {
        return false;
    }
}
