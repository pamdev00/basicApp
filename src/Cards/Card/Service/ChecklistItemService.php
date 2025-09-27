<?php

declare(strict_types=1);

namespace App\Cards\Card\Service;

use App\Cards\Card\Entity\Checklist;
use App\Cards\Card\Entity\ChecklistItem;
use App\Cards\Card\Repository\ChecklistItemRepository;
use App\Cards\Card\Request\CreateChecklistItemRequest;
use App\Cards\Card\Request\UpdateChecklistItemRequest;
use Cycle\ORM\EntityManagerInterface;
use Yiisoft\Data\Reader\DataReaderInterface;

final readonly class ChecklistItemService
{
    public function __construct(
        private ChecklistItemRepository $checklistItemRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getFullItems(string $checklistId): DataReaderInterface
    {
        return $this->checklistItemRepository->findByChecklist($checklistId);
    }
    public function getById(string $id): ?ChecklistItem
    {
        return $this->checklistItemRepository->findByPK($id);
    }

    public function create(Checklist $checklist, CreateChecklistItemRequest $request): ChecklistItem
    {
        $item = new ChecklistItem($request->getDescription());
        $item->setCompleted($request->isCompleted());
        $item->setChecklist($checklist);
        $checklist->addItem($item);

        $this->entityManager->persist($item);
        $this->entityManager->run();

        return $item;
    }

    public function update(ChecklistItem $item, UpdateChecklistItemRequest $request): ChecklistItem
    {
        $item->setDescription($request->getDescription());
        $item->setCompleted($request->isCompleted());

        $this->entityManager->persist($item);
        $this->entityManager->run();

        return $item;
    }

    public function toggle(ChecklistItem $item): ChecklistItem
    {
        $item->toggleCompleted();

        $this->entityManager->persist($item);
        $this->entityManager->run();

        return $item;
    }

    public function delete(string $id): bool
    {
        $item = $this->checklistItemRepository->findOne(['id' => $id]);

        if ($item === null) {
            return false;
        }

        $this->entityManager->delete($item);
        $this->entityManager->run();

        return true;
    }
}
