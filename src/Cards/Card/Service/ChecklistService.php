<?php

declare(strict_types=1);

namespace App\Cards\Card\Service;

use App\Cards\Card\Entity\Card;
use App\Cards\Card\Entity\Checklist;
use App\Cards\Card\Repository\ChecklistRepository;
use App\Cards\Card\Request\CreateChecklistRequest;
use App\Cards\Card\Request\UpdateChecklistRequest;
use Cycle\ORM\EntityManagerInterface;

final readonly class ChecklistService
{
    public function __construct(
        private ChecklistRepository $checklistRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getFullChecklist(string $id): ?Checklist
    {
        return $this->checklistRepository->findByIdWithItems($id);
    }

    public function create(Card $card, CreateChecklistRequest $request): Checklist
    {
        $checklist = new Checklist($request->getTitle());
        $checklist->setCard($card);
        $card->addChecklist($checklist);

        $this->entityManager->persist($checklist);
        $this->entityManager->run();

        return $checklist;
    }

    public function update(Checklist $checklist, UpdateChecklistRequest $request): Checklist
    {
        $checklist->setTitle($request->getTitle());

        $this->entityManager->persist($checklist);
        $this->entityManager->run();

        return $checklist;
    }

    public function delete(string $id): bool
    {
        $checklist = $this->checklistRepository->findOne([ 'id' => $id]);

        if ($checklist === null) {
            return false;
        }

        $this->entityManager->delete($checklist);
        $this->entityManager->run();

        return true;
    }
}
