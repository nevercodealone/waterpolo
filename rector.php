<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;


return static function (RectorConfig $rectorConfig): void {
    // get parameters
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src'
    ]);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    // Define what rule sets will be applied
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::PHP_81);
    $rectorConfig->import(SetList::NAMING);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();

    // get services (needed for register a single rule)
    $services = $rectorConfig->services();
    $services->set(ExplicitBoolCompareRector::class);

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
