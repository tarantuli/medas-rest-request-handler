<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConfigOptions;

use Medas\Core\Interfaces\ConfigGroup;
use Medas\ServiceManager\AsSingleton;

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
