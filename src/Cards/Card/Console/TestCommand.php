<?php

declare(strict_types=1);

namespace App\Cards\Card\Console;

use App\Cards\Card\Repository\TagRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class TestCommand extends Command
{
    protected static $defaultName = 'test/up';

    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Test command')
            ->setHelp('This command for test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        new SymfonyStyle($input, $output);

        $tag =  $this->tagRepository->findByName('Работа');
        var_dump($tag->getName());


        return ExitCode::OK;
    }
}
