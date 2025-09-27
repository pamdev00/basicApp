<?php

declare(strict_types=1);

namespace App\Cards\Card\Request;

use OpenApi\Attributes as OA;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

#[OA\Schema(
    schema: 'CreateChecklistItemRequest',
    required: ['description'],
    properties: [
        new OA\Property(
            property: 'description',
            description: 'Item description',
            type: 'string',
            maxLength: 1000,
            minLength: 1,
            example: 'Buy milk'
        ),
        new OA\Property(
            property: 'is_completed',
            description: 'Item completion status',
            type: 'boolean',
            default: false,
            example: false
        ),
    ],
    type: 'object'
)]
final class CreateChecklistItemRequest extends AbstractInput implements RulesProviderInterface
{
    #[Body('description')]
    #[Required]
    private string $description = '';

    #[Body('is_completed')]
    #[Required]
    #[BooleanValue]
    private bool $is_completed = false;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    public function getRules(): iterable
    {
        return [
            'description' => [
                new Length(min: 5, max: 1000),
            ],
            'is_completed' => [
                new BooleanValue(),
            ],
        ];
    }
}
