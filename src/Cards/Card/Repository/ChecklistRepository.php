<?php

declare(strict_types=1);

namespace App\Cards\Card\Repository;

use App\Cards\Card\Entity\Checklist;
use Cycle\ORM\Select;
use Throwable;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final class ChecklistRepository extends Select\Repository
{
    public function __construct(private readonly EntityWriter $entityWriter, Select $select)
    {
        parent::__construct($select);
    }

    /**
     * Get all checklists with preloaded items.
     *
     * @psalm-return DataReaderInterface<int, Checklist>
     */
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->load(['items', 'card']);

        return $this->prepareDataReader($query);
    }

    /**
     * Find checklists by card ID.
     *
     * @psalm-return DataReaderInterface<int, Checklist>
     */
    public function findByCard(string $cardId): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where(['card_id' => $cardId])
            ->load(['items']);

        return $this->prepareDataReader($query);
    }

    /**
     * Get full checklist details by ID.
     */
    public function fullChecklist(string $id): ?Checklist
    {
        return $this
            ->select()
            ->where(['id' => $id])
            ->load(['items', 'card.user'])
            ->fetchOne();
    }

    /**
     * Find checklist by ID with items.
     */
    public function findByIdWithItems(string $id): ?Checklist
    {
        return $this
            ->select()
            ->where(['id' => $id])
            ->load(['items'])
            ->fetchOne();
    }

    /**
     * Get checklist completion statistics.
     *
     * @return array{total_checklists: int, completed_checklists: int, total_items: int, completed_items: int}
     */
    public function getCompletionStats(?string $cardId = null): array
    {
        $query = $this->select()->load(['items']);

        if ($cardId !== null) {
            $query->where(['card_id' => $cardId]);
        }

        $checklists = $query->fetchAll();

        $totalChecklists = count($checklists);
        $completedChecklists = 0;
        $totalItems = 0;
        $completedItems = 0;

        foreach ($checklists as $checklist) {
            $items = $checklist->getItems();
            $totalItems += count($items);

            $checklistCompletedItems = array_filter($items, fn($item) => $item->isCompleted());
            $completedItems += count($checklistCompletedItems);

            // Checklist is considered completed if all its items are completed
            if (count($items) > 0 && count($checklistCompletedItems) === count($items)) {
                $completedChecklists++;
            }
        }

        return [
            'total_checklists' => $totalChecklists,
            'completed_checklists' => $completedChecklists,
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
        ];
    }

    /**
     * Find incomplete checklists (with at least one incomplete item).
     *
     * @psalm-return DataReaderInterface<int, Checklist>
     */
    public function findIncomplete(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->load(['items'])
            ->where(['items.is_completed' => false]);

        return $this->prepareDataReader($query);
    }

//    public function findById(string $id): ?Checklist
//    {
//        return $this
//            ->select()
//            ->where(['id' => $id])
//            ->fetchOne();
//    }

    /**
     * @throws Throwable
     */
    public function save(Checklist $checklist): void
    {
        $this->entityWriter->write([$checklist]);
    }

    /**
     * @throws Throwable
     */
    public function delete(Checklist $checklist): void
    {
        $this->entityWriter->delete([$checklist]);
    }

    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'title', 'card_id'])
                ->withOrder(['title' => 'asc'])
        );
    }
}
