<?php

declare(strict_types=1);

use App\Auth\EmailVerificationToken;
use App\Auth\EmailVerificationTokenRepositoryInterface;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORMInterface;

return [
    EmailVerificationTokenRepositoryInterface::class => static fn (ORMInterface $orm) => $orm->getRepository(EmailVerificationToken::class),

    // Replace Factory definition to redefine default collection type
    // Todo: remove with https://github.com/yiisoft/yii-cycle/issues/111
    FactoryInterface::class => static fn (DatabaseManager $dbManager, Spiral\Core\FactoryInterface $factory) => new Factory(
        $dbManager,
        null,
        $factory,
        new DoctrineCollectionFactory()
    ),
];
