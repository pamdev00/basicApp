<?php

declare(strict_types=1);


use App\Auth\RegistrationMailer;
use App\Auth\RegistrationMailerInterface;

/** @var array $params */
return [
    RegistrationMailer::class => [
        'class' => RegistrationMailer::class,
        '__construct()' => [
            'senderEmail' => $params['mailer']['senderEmail'],
        ],
    ],
    RegistrationMailerInterface::class => RegistrationMailer::class,
];
