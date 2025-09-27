<?php

declare(strict_types=1);

namespace App\Cards\Card\Service;

use App\Cards\Card\Entity\Card;
use App\Cards\Card\Repository\CardRepository;
use App\Cards\Card\Repository\TagRepository;
use App\Cards\Card\Request\EditCardRequest;
use DateMalformedStringException;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Paginator\PaginatorInterface;

class CardService
{
    public function __construct(
        private readonly CardRepository $cardRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    /**
     * Создание новой карточки
     * @throws DateMalformedStringException|Throwable
     */
    public function create(EditCardRequest $cardRequest): Card
    {
        $card = new Card();
        $card->setTitle($cardRequest->getTitle());
        $card->setDescription($cardRequest->getDescription());
        $card->setStatus($cardRequest->getStatus() ?? 'todo');
        $card->setPriority($cardRequest->getPriority() ?? 'medium');

        $card->setId(Uuid::uuid7());


        $tags = [];
        foreach ($cardRequest->getTags() as $tag) {

            $tags[] = $this->tagRepository->findByName($tag);

        }
        $card->setTags($tags);

        $this->cardRepository->save($card);

        return $card;
    }

    /**
     * Обновление карточки
     * @throws DateMalformedStringException|Throwable
     */
    public function update(Card $card, EditCardRequest $cardRequest): Card
    {

        $card->setTitle($cardRequest->getTitle());
        $card->setDescription($cardRequest->getDescription());
        $card->setStatus($cardRequest->getStatus());
        $card->setPriority($cardRequest->getPriority());

        $this->cardRepository->save($card);


        return $card;
    }

    /**
     * Удаление карточки
     */
    public function delete(string $id): bool
    {
        $card = $this->cardRepository->findOne(['id' => $id]);
        if (!$card) {
            return false;
        }

        $this->cardRepository->delete($card);
        return true;
    }

    /**
     * Получение карточки по ID с полной загрузкой
     */
    public function getFullCard(string $id): ?Card
    {

        return $this->cardRepository->fullCard($id);
    }

    private const int POSTS_PER_PAGE = 5;
    /**
     * Получение всех карточек с предзагрузкой
     */
    public function getAllPreloaded(int $page, ?array $params = null): PaginatorInterface
    {
        $dataReader = $this->cardRepository->findAllPreloaded($params);

        /** @psalm-var PaginatorInterface<array-key, Card> */
        return (new OffsetPaginator($dataReader))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($page);
    }
}
