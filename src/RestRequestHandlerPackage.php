<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler;

use Medas\EntityManager\EntityManagerPackage;
use Medas\HttpRequestHandler\HttpRequestHandlerPackage;
use Medas\Routing\RoutingPackage;
use Medas\ServiceManager\{AsSingleton, BasePackage};

class RestRequestHandlerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            EntityManagerPackage::instance(),
            RoutingPackage::instance(),
            HttpRequestHandlerPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
