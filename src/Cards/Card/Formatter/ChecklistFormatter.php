<?php

declare(strict_types=1);

namespace App\Cards\Card\Formatter;

use App\Cards\Card\Entity\Checklist;
use App\Cards\Card\Entity\ChecklistItem;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Checklist',
    properties: [
        new OA\Property(property: 'id', type: 'int', example: '100'),
        new OA\Property(property: 'title', type: 'string', example: 'Title'),
    ]
)]
final readonly class ChecklistFormatter
{
    public function format(Checklist $checklist): array
    {
        return [
            'id' => $checklist->getId(),
            'title' => $checklist->getTitle(),
            'completed_items_count' => $checklist->getCompletedItemsCount(),
            'total_items_count' => $checklist->getTotalItemsCount(),
            'completion_percentage' => $checklist->getCompletionPercentage(),
            'created_at' => $checklist->getCreatedAt()->format('d.m.Y H:i:s'),
            'updated_at' => $checklist->getUpdatedAt()->format('d.m.Y H:i:s'),
        ];
    }

    public function formatDetailed(Checklist $checklist): array
    {
        $items = [];
        foreach ($checklist->getItems() as $item) {
            $items[] = $this->formatItem($item);
        }

        return [
            'id' => $checklist->getId(),
            'title' => $checklist->getTitle(),
            'completed_items_count' => $checklist->getCompletedItemsCount(),
            'total_items_count' => $checklist->getTotalItemsCount(),
            'completion_percentage' => $checklist->getCompletionPercentage(),
            'items' => $items,
            'created_at' => $checklist->getCreatedAt()->format('d.m.Y H:i:s'),
            'updated_at' => $checklist->getUpdatedAt()->format('d.m.Y H:i:s'),
        ];
    }

    private function formatItem(ChecklistItem $item): array
    {
        return [
            'id' => $item->getId(),
            'description' => $item->getDescription(),
            'is_completed' => $item->isCompleted(),
            'created_at' => $item->getCreatedAt()->format('d.m.Y H:i:s'),
            'updated_at' => $item->getUpdatedAt()->format('d.m.Y H:i:s'),
        ];
    }
}
