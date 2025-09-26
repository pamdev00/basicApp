<?php

declare(strict_types=1);

use App\Auth\Listener\UserRegisteredListener;

/** @var array $params */

return [
    UserRegisteredListener::class => [
        '__construct()' => [
            'verificationRouteName' => $params['app']['auth']['verificationRouteName'],
        ],
    ],
];
