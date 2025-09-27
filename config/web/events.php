<?php

declare(strict_types=1);

use App\Auth\Listener\UserRegisteredListener;
use App\Auth\UserRegistered;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\Middleware\Event\SetLocaleEvent;

return [
    SetLocaleEvent::class => [
        static fn (TranslatorInterface $translator, SetLocaleEvent $event) => $translator->setLocale($event->getLocale()),
    ],
    UserRegistered::class => [
        UserRegisteredListener::class,
    ],
];
