<?php

declare(strict_types=1);

namespace App\Cards\Card\Entity;

use App\Cards\Card\Repository\ChecklistRepository;
use App\Cards\Card\Scope\PublicScope;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\ORM\Entity\Behavior;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid7;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;

#[Entity(
    repository: ChecklistRepository::class,
    scope: PublicScope::class
)
]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]

#[Uuid7(field: 'id', nullable: false)]

class Checklist
{
    #[Column(type: 'uuid', primary: true)]
    private UuidInterface $id;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $updated_at = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $created_at = null;

    #[BelongsTo(target: Card::class, nullable: false)]
    private ?Card $card = null;


    /**
     * @var Collection<array-key, ChecklistItem>
     */
    #[HasMany(target: ChecklistItem::class, fkAction: 'CASCADE')]
    private readonly Collection $items;

    public function __construct(#[Column(type: 'string(255)')]
    private string $title = '')
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }
    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(Card $card): void
    {
        $this->card = $card;
    }

    /**
     * @return ChecklistItem[]
     */
    public function getItems(): array
    {
        return $this->items->toArray();
    }

    public function addItem(ChecklistItem $item): void
    {
        $this->items->add($item);
        $item->setChecklist($this);
    }

    public function removeItem(ChecklistItem $item): void
    {
        $this->items->removeElement($item);
    }

    public function getCompletedItemsCount(): int
    {
        return count(array_filter($this->getItems(), static fn (ChecklistItem $item) => $item->isCompleted()));
    }

    public function getTotalItemsCount(): int
    {
        return $this->items->count();
    }

    public function getCompletionPercentage(): float
    {
        $total = $this->getTotalItemsCount();
        if ($total === 0) {
            return 0.0;
        }

        return ($this->getCompletedItemsCount() / $total) * 100;
    }

    public function isNewRecord(): bool
    {
        return $this->getId() === null;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
