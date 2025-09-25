<?php

declare(strict_types=1);

namespace App\User;

use App\User\Enum\UserStatus;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior\CreatedAt;
use Cycle\ORM\Entity\Behavior\UpdatedAt;
use DateTimeImmutable;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Security\PasswordHasher;
use Yiisoft\Security\Random;

#[Entity(repository: UserRepository::class)]
#[Index(columns: ['login'], unique: true)]
#[Index(columns: ['token'], unique: true)]
#[CreatedAt(field: 'created_at', column: 'created_at')]
#[UpdatedAt(field: 'updated_at', column: 'updated_at')]
class User implements IdentityInterface
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string(128)')]
    private string $token;

    #[Column(type: 'string(48)')]
    private string $login;

    #[Column(type: 'string')]
    private string $passwordHash;

    #[Column(type: 'string(100)', unique: true)]
    private string $email;

    #[Column(type: 'integer', typecast: UserStatus::class, default: 1)]
    private UserStatus $status;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $emailVerifiedAt = null;

    #[Column(type: 'string(16)', default: 'en-US')]
    private string $locale = 'en-US';

    #[Column(type: 'string(64)', default: 'UTC')]
    private string $timezone = 'UTC';

    private DateTimeImmutable $created_at;

    private DateTimeImmutable $updated_at;

    public function __construct(
        string $login,
        string $password,
        string $email,
        string $locale = 'en-US',
        string $timezone = 'UTC'
    ) {
        $this->login = $login;
        $this->email = $email;
        $this->locale = $locale;
        $this->timezone = $timezone;
        $this->status = UserStatus::WAITING_VERIFICATION;
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
        $this->setPassword($password);
        $this->resetToken();
    }

    public function getId(): ?string
    {
        return $this->id === null ? null : (string) $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function resetToken(): void
    {
        $this->token = Random::string(128);
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function validatePassword(string $password): bool
    {
        return (new PasswordHasher())->validate($password, $this->passwordHash);
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash = (new PasswordHasher())->hash($password);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function activate(): void
    {
        $this->status = UserStatus::ACTIVE;
        $this->emailVerifiedAt = new DateTimeImmutable();
    }
}
