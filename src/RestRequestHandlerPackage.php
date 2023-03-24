<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler;

use Medas\EntityManager\EntityManagerPackage;
use Medas\HttpRequestHandler\HttpRequestHandlerPackage;
use Medas\RestRequestHandler\Serializers\ArgumentDeserializer;
use Medas\Routing\RoutingPackage;
use Medas\ServiceManager\{AsSingleton, BasePackage, ServiceConfig};

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

    public function initialize(ServiceConfig $config): void
    {
        parent::initialize($config);
        $config->addArgumentProcessor(service(ArgumentDeserializer::class));
    }
}
