<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions\ClassGenerators;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\RestRequestHandler\ConfigOptions\RestRequestHandlerGroup;

#[Service]
readonly class ClassGeneratorGroup implements ConfigGroup
{
    public function __construct(
        private RestRequestHandlerGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'class-generators';
    }
}
