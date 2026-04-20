<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions\ClassGenerators;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class NormalizerClassNamePattern implements ConfigOption
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
        return 'normalizer-class-name-pattern';
    }

    /**
     * You can use the following placeholders:
     * - {{psr4Prefix}}: The PSR-4 prefix of the entity class
     * - {{subPath}}: The sub-path of the entity class, everything between the PSR-4 prefix and the entity class name
     *
     * You MUST use the following placeholders at least once:
     * - {{entityName}}: The name of the entity class, everything after the last backslash
     */
    public function description(): string
    {
        return 'The pattern to use to generate the class name of the entity normalizer';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '{{psr4Prefix}}\RestControllers{{subPath}}\{{entityName}}\Helpers\Normalizer';
    }
}
