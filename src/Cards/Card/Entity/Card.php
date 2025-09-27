<?php

declare(strict_types=1);

namespace App\Cards\Card\Entity;

use App\Cards\Card\Repository\CardRepository;
use App\Cards\Card\Scope\PublicScope;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid7;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;

#[Entity(
    repository: CardRepository::class,
    scope: PublicScope::class
)]
#[Index(columns: ['status', 'priority', 'due_date'])]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]
#[Uuid7(field: 'id', nullable: false)]
class Card
{
    #[Column(type: 'uuid', primary: true)]
    private UuidInterface $id;

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    #[Column(type: 'string(50)', default: 'todo')]
    private string $status = 'todo';

    #[Column(type: 'string(50)', default: 'medium')]
    private string $priority = 'medium';

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $due_date = null;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $updated_at;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    //    #[BelongsTo(target: User::class, nullable: false)]
    //    private ?User $user = null;
    //    private ?string $user_id = null;

    #[Cycle\HasMany(
        target: CardTag::class,
        innerKey: 'id',
        outerKey: 'card_id',
        fkAction: 'CASCADE'
    )]
    private readonly Collection $cardTags;

    /**
     * @var Collection<array-key, Checklist>
     */
    #[HasMany(target: Checklist::class, fkAction: 'CASCADE')]
    private readonly Collection $checklists;
    public function __construct(#[Column(type: 'string(255)')]
    private string $title = '', #[Column(type: 'text', nullable: true)]
    private ?string $description = null)
    {
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();

        $this->checklists = new ArrayCollection();
        $this->cardTags = new ArrayCollection();

    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
    }

    public function getDueDate(): ?DateTimeImmutable
    {
        return $this->due_date;
    }

    public function setDueDate(?DateTimeImmutable $due_date): void
    {
        $this->due_date = $due_date;
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

    //    public function setUser(User $user): void
    //    {
    //        $this->user = $user;
    //    }
    //
    //    public function getUser(): ?User
    //    {
    //        return $this->user;
    //    }


    public function isNewRecord(): bool
    {
        return $this->getId() === null;
    }

    /**
     * @return Checklist[]
     */
    public function getChecklists(): array
    {
        return $this->checklists->toArray();
    }

    public function addChecklist(Checklist $checklist): void
    {
        $this->checklists->add($checklist);
        $checklist->setCard($this);
    }

    public function removeChecklist(Checklist $checklist): void
    {
        $this->checklists->removeElement($checklist);
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return array_map(
            static fn (CardTag $cardTag) => $cardTag->getTag(),
            $this->cardTags->toArray()
        );
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags(array $tags): void
    {
        $this->cardTags->clear();
        foreach ($tags as $tag) {
            $this->cardTags->add(new CardTag($this, $tag));
        }
    }

    public function addTag(Tag $tag): void
    {
        foreach ($this->cardTags as $cardTag) {
            if ($cardTag->getTag()->getId() === $tag->getId()) {
                return; // tag already linked
            }
        }

        $this->cardTags->add(new CardTag($this, $tag));
    }

    public function removeTag(Tag $tag): void
    {
        foreach ($this->cardTags as $cardTag) {
            if ($cardTag->getTag()->getId() === $tag->getId()) {
                $this->cardTags->removeElement($cardTag);
                return;
            }
        }
    }
}
