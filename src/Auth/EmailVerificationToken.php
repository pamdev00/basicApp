<?php

declare(strict_types=1);

namespace App\Auth;

use App\User\User;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\ORM\Entity\Behavior\CreatedAt;
use DateTimeImmutable;

#[Entity(repository: CycleEmailVerificationTokenRepository::class)]
#[CreatedAt(field: 'createdAt', column: 'created_at')]
class EmailVerificationToken
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $usedAt = null;

    private readonly DateTimeImmutable $createdAt;

    public function __construct(#[Column(type: 'string')]
        private readonly string $tokenHash, #[Column(type: 'datetime')]
        private readonly DateTimeImmutable $expiresAt, #[BelongsTo(target: User::class)]
        private readonly User $user)
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUsedAt(): ?DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function markAsUsed(): void
    {
        $this->usedAt = new DateTimeImmutable();
    }
}
