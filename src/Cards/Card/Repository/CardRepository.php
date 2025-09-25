<?php

declare(strict_types=1);

namespace App\Cards\Card\Repository;

use App\Cards\Card\Entity\Card;
use Cycle\ORM\Select;
use DateTimeImmutable;
use DateTimeInterface;
use Throwable;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final class CardRepository extends Select\Repository
{
    public function __construct(private readonly EntityWriter $entityWriter, Select $select)
    {
        parent::__construct($select);
    }

    /**
     * Get cards without filter with preloaded relations.
     *
     * @psalm-return DataReaderInterface<int, Card>
     */
    public function findAllPreloaded(?array $params = null): DataReaderInterface
    {
        $query = $this
            ->select();
//            ->load(['user',
//                'tags',
//                'checklists.items'
//            ]);
        if($params){
            // todo для params лучше сделать отдельный фильтр типа CardFilter в котором хранить все эти параметры
            $query->andWhere(array_filter([
                'status' => $params['status'] ?? null,
                'priority' => $params['priority'] ?? null
            ]));
        }
        return $this->prepareDataReader($query);
    }

    /**
     * Get full card details by ID.
     */
    public function fullCard(string $id): ?Card
    {
        return $this->findOne(['id' => $id]);
    }



    public function findById(string $id): ?Card
    {
        return $this
            ->select()
            ->where(['id' => $id])
            ->fetchOne();
    }

    /**
     * @throws Throwable
     */
    public function save(Card $card): void
    {
        $this->entityWriter->write([$card]);
    }

    /**
     * @throws Throwable
     */
    public function delete(Card $card): void
    {
        $this->entityWriter->delete([$card]);
    }


    /**
     * @psalm-return EntityReader<array-key, Card>
     */
    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'title', 'status', 'priority', 'due_date', 'updated_at', 'created_at'])
                ->withOrder(['created_at' => 'desc'])
        );
    }
}
