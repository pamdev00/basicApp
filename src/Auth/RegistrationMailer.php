<?php

declare(strict_types=1);

namespace App\Auth;

use Psr\Log\LoggerInterface;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Message;

final readonly class RegistrationMailer
{
    public function __construct(
        private LoggerInterface $logger,
        private MailerInterface $mailer,
        private string $senderEmail,
    ) {
    }

    public function send(string $to, string $subject, string $htmlBody): void
    {
        $this->logger->info('Mailer class: ' . get_class($this->mailer));

        $message = (new Message())
            ->withFrom($this->senderEmail)
            ->withTo($to)
            ->withSubject($subject)
            ->withHtmlBody($htmlBody)
            ->withTextBody($htmlBody);

        $this->mailer->send($message);
        $this->logger->info(
            'Email has been sent to {email}.',
            ['email' => $to]
        );
    }
}
