<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\Core\Interfaces\ConfigManager;
use Medas\Events\EventsPackage;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\RestRequestHandler\RestRequestHandlerPackage;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class, isDev: true);

    $config->addPackages([
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        EventsPackage::instance(),
        RamseyUuidBridgePackage::instance(),
        RestRequestHandlerPackage::instance(),
    ]);

    return $config;
});

// The entities the filtering tests select: a filter or sort name is checked against
// real entity classes and stores
service(ConfigManager::class)
    ->addDirectory(__DIR__ . '/tests/MockUps/config');
