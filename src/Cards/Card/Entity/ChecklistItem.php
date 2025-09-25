<?php

declare(strict_types=1);

namespace App\Cards\Card\Entity;

use App\Cards\Card\Repository\ChecklistItemRepository;
use App\Cards\Card\Scope\PublicScope;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\ORM\Entity\Behavior;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid7;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

#[Entity(
    repository: ChecklistItemRepository::class,
    scope: PublicScope::class)
]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]
#[Uuid7(field: 'id', nullable: false)]

class ChecklistItem
{
    #[Column(type: 'uuid', primary: true)]
    private UuidInterface $id;

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'bool', default: 'false', typecast: 'bool')]
    private bool $is_completed = false;

    #[BelongsTo(target: Checklist::class, nullable: false)]
    private ?Checklist $checklist = null;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $updated_at;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    public function __construct(string $description = '')
    {
        $this->description = $description;
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    public function setCompleted(bool $is_completed): void
    {
        $this->is_completed = $is_completed;
    }

    public function getChecklist(): ?Checklist
    {
        return $this->checklist;
    }

    public function setChecklist(Checklist $checklist): void
    {
        $this->checklist = $checklist;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function toggleCompleted(): void
    {
        $this->is_completed = !$this->is_completed;
    }

    public function isNewRecord(): bool
    {
        return $this->getId() === null;
    }
}
