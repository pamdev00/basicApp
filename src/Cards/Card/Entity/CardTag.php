<?php

declare(strict_types=1);

namespace App\Cards\Card\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid7;
use Ramsey\Uuid\UuidInterface;

#[Entity]
#[Uuid7(field: 'id', nullable: false)]
class CardTag
{
    #[Column(type: 'uuid', primary: true)]
    private ?UuidInterface $id = null;


    public function __construct(
        #[BelongsTo(target: Card::class, nullable: false)]
        private ?Card $card,
        #[BelongsTo(target: Tag::class, nullable: false)]
        private ?Tag $tag
    ) {
    }
    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(Card $card): void
    {
        $this->card = $card;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }
}
