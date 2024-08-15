<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\Events\EventsPackage;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\RestRequestHandler\RestRequestHandlerPackage;
use Medas\ServiceManager\ServiceConfig;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        RestRequestHandlerPackage::instance(),
        ConfigManagerPackage::instance(),
        ConfigOptionsPackage::instance(),
        EventsPackage::instance(),
        RamseyUuidBridgePackage::instance(),
    ]);

    return $config;
});
