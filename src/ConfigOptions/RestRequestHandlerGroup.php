<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\ConfigGroup;

#[Service]
class RestRequestHandlerGroup implements ConfigGroup
{
    use AsSingleton;

    public function parent(): ConfigGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'rest-request-handler';
    }
}
