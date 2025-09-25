<?php

declare(strict_types=1);

use App\Auth\AuthRequestErrorHandler;
use App\User\UserRepository;
use App\User\UserRepositoryInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;
use Yiisoft\Auth\Method\HttpHeader;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    UserRepositoryInterface::class => UserRepository::class,
    IdentityRepositoryInterface::class => UserRepository::class,
    IdentityWithTokenRepositoryInterface::class => UserRepository::class,
    AuthenticationMethodInterface::class => HttpHeader::class,
    Authentication::class => [
        'class' => Authentication::class,
        '__construct()' => [
            'authenticationFailureHandler' => Reference::to(AuthRequestErrorHandler::class),
        ],
    ],
];
