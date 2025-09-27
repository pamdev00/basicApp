<?php

declare(strict_types=1);

namespace App\Cards\Card\Service;

use App\Cards\Card\Entity\Tag;
use App\Cards\Card\Repository\TagRepository;
use Yiisoft\Data\Reader\DataReaderInterface;

class TagService
{
    public function __construct(
        private readonly TagRepository $tagRepository
    ) {
    }

    /**
     * Создание нового тега
     */
    public function create(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName(trim($name));

        $this->tagRepository->save($tag);
        return $tag;
    }

    /**
     * Обновление тега
     */
    public function update(string $id, string $name): ?Tag
    {
        $tag = $this->tagRepository->findById($id);
        if (!$tag) {
            return null;
        }

        $tag->setName(trim($name));
        $this->tagRepository->save($tag);

        return $tag;
    }

    /**
     * Удаление тега
     */
    public function delete(string $id): bool
    {
        $tag = $this->tagRepository->findById($id);
        if (!$tag) {
            return false;
        }

        $this->tagRepository->delete($tag);
        return true;
    }

    /**
     * Получение всех тегов
     */
    public function getAll(): DataReaderInterface
    {
        return $this->tagRepository->findAll();
    }

    /**
     * Получение всех тегов с карточками
     */
    public function getAllPreloaded(): DataReaderInterface
    {
        return $this->tagRepository->findAllPreloaded();
    }

    /**
     * Поиск тега по имени
     */
    public function findByName(string $name): ?Tag
    {
        return $this->tagRepository->findByName($name);
    }

    /**
     * Получение тега с карточками
     */
    public function getWithCards(string $id): ?Tag
    {
        return $this->tagRepository->findByIdWithCards($id);
    }

    /**
     * Поиск тегов по паттерну
     */
    public function search(string $pattern): DataReaderInterface
    {
        return $this->tagRepository->searchByName($pattern);
    }

    /**
     * Найти или создать тег
     */
    public function findOrCreate(string $name): Tag
    {
        return $this->tagRepository->findOrCreate(trim($name));
    }

    /**
     * Получение статистики использования тегов
     */
    public function getUsageStats(): array
    {
        return $this->tagRepository->getUsageStats();
    }

    /**
     * Получение популярных тегов
     */
    public function getPopularTags(int $limit = 10): array
    {
        $stats = $this->getUsageStats();

        // Сортируем по количеству использований
        usort($stats, fn ($a, $b) => $b['usage_count'] <=> $a['usage_count']);

        return array_slice($stats, 0, $limit);
    }

    /**
     * Массовое создание тегов
     */
    public function bulkCreate(array $tagNames): array
    {
        $tags = [];

        foreach ($tagNames as $name) {
            $name = trim((string)$name);
            if (!empty($name)) {
                $tags[] = $this->findOrCreate($name);
            }
        }

        return $tags;
    }
}
