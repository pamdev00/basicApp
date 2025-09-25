<?php

declare(strict_types=1);

namespace App\User;

use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;
use Yiisoft\Data\Cycle\Reader\EntityReader;

interface UserRepositoryInterface extends IdentityWithTokenRepositoryInterface, IdentityRepositoryInterface
{
    /**
     * @psalm-return EntityReader<array-key, User>
     */
    public function findAllOrderByLogin(): EntityReader;

    public function findByLogin(string $login): ?IdentityInterface;

    public function findByEmail(string $email): ?IdentityInterface;

    public function save(IdentityInterface $user): void;
}
