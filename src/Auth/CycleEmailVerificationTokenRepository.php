<?php

declare(strict_types=1);

namespace App\Auth;

use Cycle\ORM\Select;
use Yiisoft\Data\Cycle\Writer\EntityWriter;

final class CycleEmailVerificationTokenRepository extends Select\Repository implements EmailVerificationTokenRepositoryInterface
{
    public function __construct(
        Select $select,
        private EntityWriter $entityWriter,
    ) {
        parent::__construct($select);
    }

    public function findByHash(string $tokenHash): ?EmailVerificationToken
    {
        return $this->findOne(['tokenHash' => $tokenHash]);
    }

    public function save(EmailVerificationToken $token): void
    {
        $this->entityWriter->write([$token]);
    }

    public function findAllExpiredOrUsed(): iterable
    {
        return $this
            ->select()
            ->where('usedAt', '!=', null)
            ->orWhere('expiresAt', '<', new \DateTimeImmutable())
            ->fetchAll();
    }
}
