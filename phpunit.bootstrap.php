<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigOptions\ConfigOptionsPackage;

require_once __DIR__ . '/bootstrap.php';

sm()
    ->addPackage(ConfigManagerPackage::instance())
    ->addPackage(ConfigOptionsPackage::instance());
