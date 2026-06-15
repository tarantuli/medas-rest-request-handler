<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\Events\EventsPackage;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\RestRequestHandler\RestRequestHandlerPackage;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        RestRequestHandlerPackage::instance(),
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        EventsPackage::instance(),
        RamseyUuidBridgePackage::instance(),
    ]);

    return $config;
});
