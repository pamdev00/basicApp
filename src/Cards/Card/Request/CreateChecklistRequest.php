<?php

declare(strict_types=1);

namespace App\Cards\Card\Request;

use OpenApi\Attributes as OA;
use Yiisoft\Hydrator\Validator\Attribute\Validate;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

#[OA\Schema(
    schema: 'CreateChecklistRequest',
    required: ['title'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Checklist title',
            type: 'string',
            maxLength: 255,
            minLength: 1,
            example: 'Shopping List'
        ),
    ],
    type: 'object'
)]
final class CreateChecklistRequest extends AbstractInput implements RulesProviderInterface
{
    #[Body('title')]
    #[Validate(new Required())]
    private string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getRules(): array
    {
        return [
            'title' => [
                new Length(min: 5, max: 255),
            ],
        ];
    }
}
