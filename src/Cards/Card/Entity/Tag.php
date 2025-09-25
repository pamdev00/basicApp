<?php

declare(strict_types=1);

namespace App\Cards\Card\Entity;

use App\Cards\Card\Repository\TagRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Collection\Pivoted\PivotedCollection;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid7;
use Ramsey\Uuid\UuidInterface;

#[Entity(repository: TagRepository::class)]
#[Index(columns: ['name'], unique: true)]
#[Uuid7(field: 'id', nullable: false)]
class Tag
{
    #[Column(type: 'uuid', primary: true)]
    private UuidInterface $id;

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    #[Column(type: 'string(100)')]
    private string $name = '';

    /**
     * @var PivotedCollection<array-key, Card, CardTag>
     */
    #[ManyToMany(target: Card::class, though: CardTag::class)]
    private PivotedCollection $cards;

    public function __construct(string $name = '')
    {
        $this->name = $name;
        $this->cards = new PivotedCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards->toArray();
    }

    public function addCard(Card $card): void
    {
        $this->cards->add($card);
    }

    public function removeCard(Card $card): void
    {
        $this->cards->removeElement($card);
    }

    public function isNewRecord(): bool
    {
        return $this->getId() === null;
    }
}
