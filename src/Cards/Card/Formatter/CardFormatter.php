<?php

declare(strict_types=1);

namespace App\Cards\Card\Formatter;

use App\Cards\Card\Entity\Card;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Card',
    properties: [
        new OA\Property(property: 'id', type: 'int', example: '100'),
        new OA\Property(property: 'title', type: 'string', example: 'Title'),
        new OA\Property(property: 'description', type: 'string', example: 'Text'),
        new OA\Property(property: 'status', type: 'string', example: 'todo'),
    ]
)]
final class CardFormatter
{
    public function format(Card $card): array
    {
        return [
            'id' => $card->getId(),
            'title' => $card->getTitle(),
            'description' => $card->getDescription(),
            'status' => $card->getStatus(),
        ];
    }
}
