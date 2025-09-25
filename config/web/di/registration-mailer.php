<?php

declare(strict_types=1);


use App\Auth\RegistrationMailer;

/** @var array $params */
return [
    RegistrationMailer::class => [
        'class' => RegistrationMailer::class,
        '__construct()' => [
            'senderEmail' => $params['mailer']['senderEmail'],
        ],
    ],
];
