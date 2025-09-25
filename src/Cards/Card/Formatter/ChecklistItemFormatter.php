<?php

declare(strict_types=1);

namespace App\Cards\Card\Formatter;

use App\Cards\Card\Entity\Checklist;
use App\Cards\Card\Entity\ChecklistItem;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChecklistItem',
    properties: [
        new OA\Property(property: 'id', type: 'int', example: '100'),
        new OA\Property(property: 'description', type: 'string', example: 'Title'),
    ]
)]
final readonly class ChecklistItemFormatter
{
    public function format(ChecklistItem $item): array
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
