<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions\ComparisonOperators;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};

#[Service]
readonly class ComparisonOperatorGroup implements ConfigGroup
{
    public function parent(): ConfigGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'comparison-operators';
    }
}
