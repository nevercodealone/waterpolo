<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;


return static function (RectorConfig $rectorConfig): void {
    // get parameters
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src'
    ]);

    // Define what rule sets will be applied
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::DEAD_CODE,
        SetList::PHP_81,
        SetList::NAMING
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();

    // get services (needed for register a single rule)
    $services = $rectorConfig->services();
    $services->set(ExplicitBoolCompareRector::class);

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
