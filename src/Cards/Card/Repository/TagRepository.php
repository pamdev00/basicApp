<?php

declare(strict_types=1);

namespace App\Cards\Card\Repository;

use App\Cards\Card\Entity\Tag;
use Cycle\ORM\Select;
use Throwable;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final class TagRepository extends Select\Repository
{
    public function __construct(private readonly EntityWriter $entityWriter, Select $select)
    {
        parent::__construct($select);
    }

    /**
     * Get all tags with preloaded cards.
     *
     * @psalm-return DataReaderInterface<int, Tag>
     */
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->load(['cards']);

        return $this->prepareDataReader($query);
    }


    /**
     * Find tag by name.
     */
    public function findByName(string $name): ?Tag
    {
        return $this
            ->select()
            ->andWhere(['name' => $name])
            ->fetchOne();
    }

    /**
     * Find tag by ID with cards.
     */
    public function findByIdWithCards(string $id): ?Tag
    {
        return $this
            ->select()
            ->where(['id' => $id])
            ->load(['cards.user'])
            ->fetchOne();
    }

    /**
     * Get tags usage statistics.
     *
     * @return array<string, array{id: string, name: string, cards_count: int}>
     */
    public function getUsageStats(): array
    {
        $tags = $this
            ->select()
            ->load(['cards'])
            ->fetchAll();

        $stats = [];
        foreach ($tags as $tag) {
            $stats[$tag->getName()] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'cards_count' => count($tag->getCards()),
            ];
        }

        // Sort by usage count descending
        uasort($stats, fn ($a, $b) => $b['cards_count'] <=> $a['cards_count']);

        return $stats;
    }

    /**
     * Find or create tag by name.
     *
     * @throws Throwable
     */
    public function findOrCreate(string $name): Tag
    {
        $tag = $this->findByName($name);

        if ($tag === null) {
            $tag = new Tag($name);
            $this->save($tag);
        }

        return $tag;
    }

    /**
     * Search tags by name pattern.
     *
     * @psalm-return DataReaderInterface<int, Tag>
     */
    public function searchByName(string $pattern): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where('name', 'ILIKE', "%{$pattern}%");

        return $this->prepareDataReader($query);
    }

    public function findById(string $id): ?Tag
    {
        return $this
            ->select()
            ->where(['id' => $id])
            ->fetchOne();
    }

    /**
     * @throws Throwable
     */
    public function save(Tag $tag): void
    {
        $this->entityWriter->write([$tag]);
    }

    /**
     * @throws Throwable
     */
    public function delete(Tag $tag): void
    {
        $this->entityWriter->delete([$tag]);
    }

    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'name'])
                ->withOrder(['name' => 'asc'])
        );
    }
}
