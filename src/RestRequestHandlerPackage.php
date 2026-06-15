<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler;

use Medas\Core\{AsSingleton, BasePackage, Interfaces\ServiceConfigBuilder};
use Medas\EntityManager\EntityManagerPackage;
use Medas\HttpRequestHandler\HttpRequestHandlerPackage;
use Medas\Routing\RoutingPackage;

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

    public function initialize(ServiceConfigBuilder $config): void
    {
        parent::initialize($config);

        $config->addArgumentProcessor(Serializers\ArgumentDeserializer::class);
    }
}
