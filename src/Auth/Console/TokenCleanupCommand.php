<?php

declare(strict_types=1);

namespace App\Auth\Console;

use App\Auth\EmailVerificationTokenRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    name: 'token:cleanup',
    description: 'Deletes expired and used email verification tokens.'
)]
final class TokenCleanupCommand extends Command
{
    public function __construct(
        private readonly EmailVerificationTokenRepositoryInterface $tokenRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command finds and deletes all email verification tokens that are either expired or have already been used.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tokens = $this->tokenRepository->findAllExpiredOrUsed();
        $count = 0;

        foreach ($tokens as $token) {
            $this->entityManager->delete($token);
            $count++;
        }

        if ($count > 0) {
            $this->entityManager->run();
            $io->success(sprintf('Deleted %d expired/used tokens.', $count));
        } else {
            $io->info('No expired or used tokens to delete.');
        }

        return ExitCode::OK;
    }
}
