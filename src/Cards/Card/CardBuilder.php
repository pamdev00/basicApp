<?php

declare(strict_types=1);

namespace App\Cards\Card;

use App\Cards\Card\Entity\Card;
use App\Cards\Card\Repository\CardRepository;
use App\Cards\Card\Repository\TagRepository;
use App\Cards\Card\Request\EditCardRequest;

final class CardBuilder
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    )
    {
    }

    public function build(Card $card, EditCardRequest $request): Card
    {
        $card->setTitle($request->getTitle());
        $card->setDescription($request->getDescription());
        $card->setStatus($request->getStatus());

        $tags = [];
        foreach ($request->getTags() as $tag) {
            $tags = $this->tagRepository->findAll(['name' => $tag]);

        }
        $card->setTags($tags);
        return $card;
    }
}
