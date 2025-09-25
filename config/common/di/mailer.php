<?php

declare(strict_types=1);

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Mailer\FileMailer;
use Yiisoft\Mailer\HtmlToTextBodyConverter;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageSettings;
use Yiisoft\Mailer\Symfony\Mailer;

return [
    TransportInterface::class => static function () {
        $dsn = sprintf(
            '%s://%s:%s@%s:%d',
            $_ENV['MAILER_SCHEME'] ?? 'smtp',
            $_ENV['MAILER_USERNAME'] ?? '',
            $_ENV['MAILER_PASSWORD'] ?? '',
            $_ENV['MAILER_HOST'],
            (int)$_ENV['MAILER_PORT']
        );

        return Transport::fromDsn($dsn);
    },

    MailerInterface::class => ('dev') === 'prod'
        ? Mailer::class
        : [
            'class' => FileMailer::class,
            '__construct()' => [
                'messageSettings' =>  new MessageSettings(htmlToTextBodyConverter: new HtmlToTextBodyConverter()),
                'path' => DynamicReference::to(
                    static fn(Aliases $aliases) => $aliases->get('@runtime/mail')
                ),
            ],
        ],
];
