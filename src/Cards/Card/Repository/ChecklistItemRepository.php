<?php

declare(strict_types=1);

namespace App\Cards\Card\Repository;

use App\Cards\Card\Entity\ChecklistItem;
use App\Cards\Card\Scope\PublicScope;
use Cycle\ORM\Select;
use DateTimeImmutable;
use DateTimeInterface;
use Throwable;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final class ChecklistItemRepository extends Select\Repository
{
    public function __construct(private readonly EntityWriter $entityWriter, Select $select)
    {
        parent::__construct($select);
    }

    /**
     * Get all checklist items with preloaded checklist.
     *
     * @psalm-return DataReaderInterface<int, ChecklistItem>
     */
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->load(['checklist.card']);

        return $this->prepareDataReader($query);
    }

    /**
     * Find items by checklist ID.
     *
     * @psalm-return DataReaderInterface<int, ChecklistItem>
     */
    public function findByChecklist(string $checklistId): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where(['checklist_id' => $checklistId]);

        return $this->prepareDataReader($query);
    }

    /**
     * Find completed items by checklist ID.
     *
     * @psalm-return DataReaderInterface<int, ChecklistItem>
     */
    public function findCompletedByChecklist(string $checklistId): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where(['checklist_id' => $checklistId, 'is_completed' => true]);

        return $this->prepareDataReader($query);
    }

    /**
     * Find incomplete items by checklist ID.
     *
     * @psalm-return DataReaderInterface<int, ChecklistItem>
     */
    public function findIncompleteByChecklist(string $checklistId): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where(['checklist_id' => $checklistId, 'is_completed' => false]);

        return $this->prepareDataReader($query);
    }

    /**
     * Get items completion statistics for a checklist.
     *
     * @return array{total: int, completed: int, percentage: float}
     */
    public function getCompletionStats(string $checklistId): array
    {
        $total = $this
            ->select()
            ->where(['checklist_id' => $checklistId])
            ->count();

        $completed = $this
            ->select()
            ->where(['checklist_id' => $checklistId, 'is_completed' => true])
            ->count();

        $percentage = $total > 0 ? ($completed / $total) * 100 : 0.0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => round($percentage, 2),
        ];
    }

    /**
     * Find recently completed items.
     *
     * @psalm-return DataReaderInterface<int, ChecklistItem>
     * @throws \DateMalformedStringException
     */
    public function findRecentlyCompleted(int $days = 7): DataReaderInterface
    {
        $since = (new DateTimeImmutable())->modify("-{$days} days");

        $query = $this
            ->select()
            ->where(['is_completed' => true])
            ->where('updated_at', '>=', $since)
            ->load(['checklist.card']);

        return $this->prepareDataReader($query);
    }

    /**
     * Search items by description pattern.
     *
     * @psalm-return DataReaderInterface<int, ChecklistItem>
     */
    public function searchByDescription(string $pattern, ?string $checklistId = null): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where('description', 'ILIKE', "%{$pattern}%");

        if ($checklistId !== null) {
            $query->where(['checklist_id' => $checklistId]);
        }

        return $this->prepareDataReader($query);
    }

    /**
     * Toggle completion status of an item.
     *
     * @throws Throwable
     */
    public function toggleCompletion(string $id): ?ChecklistItem
    {
        $item = $this->findById($id);

        if ($item === null) {
            return null;
        }

        $item->toggleCompleted();
        $this->save($item);

        return $item;
    }

    /**
     * Mark multiple items as completed.
     *
     * @param string[] $itemIds
     * @throws Throwable
     */
    public function markMultipleAsCompleted(array $itemIds): void
    {
        $items = $this
            ->select()
            ->where(['id' => ['IN' => $itemIds]])
            ->fetchAll();

        foreach ($items as $item) {
            $item->setCompleted(true);
        }

        $this->entityWriter->write($items);
    }

    /**
     * Mark multiple items as incomplete.
     *
     * @param string[] $itemIds
     * @throws Throwable
     */
    public function markMultipleAsIncomplete(array $itemIds): void
    {
        $items = $this
            ->select()
            ->where(['id' => ['IN' => $itemIds]])
            ->fetchAll();

        foreach ($items as $item) {
            $item->setCompleted(false);
        }

        $this->entityWriter->write($items);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getMaxUpdatedAt(): DateTimeInterface
    {
        return new DateTimeImmutable($this
            ->select()
            ->max('updated_at') ?? 'now');
    }

    public function findById(string $id): ?ChecklistItem
    {
        return $this
            ->select()
            ->scope(new PublicScope())
            ->where(['id' => $id])
            ->fetchOne();
    }

    /**
     * @throws Throwable
     */
    public function save(ChecklistItem $item): void
    {
        $this->entityWriter->write([$item]);
    }

    /**
     * @throws Throwable
     */
    public function delete(ChecklistItem $item): void
    {
        $this->entityWriter->delete([$item]);
    }

    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'description', 'is_completed', 'created_at', 'updated_at', 'checklist_id'])
                ->withOrder(['created_at' => 'asc'])
        );
    }
}
