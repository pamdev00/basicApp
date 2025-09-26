<?php

declare(strict_types=1);

use Rector\CodeQuality\Set\CodeQualitySetList;
use Rector\CodingStyle\Set\CodingStyleSetList;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Set\DeadCodeSetList;
use Rector\Naming\Set\NamingSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/migrations',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/views',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/runtime',
        __DIR__ . '/vendor',
        '*/var/*',
        '*/cache/*',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
        CodeQualitySetList::CODE_QUALITY,
        CodingStyleSetList::SPACES,
        DeadCodeSetList::DEAD_CODE,
        NamingSetList::PSR_4,
        PHPUnitSetList::PHPUNIT_100,
    ]);
};
