<?php

declare(strict_types=1);


use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Definitions\Reference;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\CounterInterface;
use Yiisoft\Yii\RateLimiter\LimitRequestsMiddleware;
use Yiisoft\Yii\RateLimiter\Storage\SimpleCacheStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

return [
    StorageInterface::class => function (Aliases $aliases) {
        $cache = new FileCache($aliases->get('@runtime/rate-limiter'));

        return new SimpleCacheStorage($cache);
    },
    CounterInterface::class => [
        'class' => Counter::class,
        '__construct()' => [
            'limit' => 7,
            'periodInSeconds' => 10,
        ],
    ],
    LimitRequestsMiddleware::class => [
        'class' => LimitRequestsMiddleware::class,
        '__construct()' => [
            'counter' => Reference::to(CounterInterface::class),
            'responseFactory' => Reference::to(ResponseFactoryInterface::class),
        ],
    ],
];
