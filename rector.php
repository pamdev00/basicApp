<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Param\AddSensitiveParameterAttributeRector;

return RectorConfig::configure()
    ->withCache(cacheDirectory: __DIR__ . '/runtime/rector')
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/public',
        __DIR__ . '/config',
        __DIR__ . '/tests',
        __DIR__ . '/resources',
    ])
    ->withPhpSets(php83: true)
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
    ])
//    ->withConfiguredRule(AddSensitiveParameterAttributeRector::class, [
//        'sensitive_parameters' => [
//            'password',
//            'tokenHash',
//            'passwordHash',
//        ],
//    ])
    ->withPreparedSets(deadCode: true);
