<?php

declare(strict_types=1);

namespace App\Cards\Card\Request;

use OpenApi\Attributes as OA;
use Yiisoft\Hydrator\Validator\Attribute\Validate;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Type\StringType;
use Yiisoft\Validator\RulesProviderInterface;

#[OA\Schema(
    schema: 'EditCardRequest',
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Title card'),
        new OA\Property(property: 'description', type: 'string', example: 'Text card'),
        new OA\Property(property: 'status', type: 'string', example: 'todo'),
        new OA\Property(property: 'priority', type: 'string', enum: ["hot", "high", "low"], example: 'medium'),
        new OA\Property(property: 'tags', type: 'string', enum: ["Работа", "Личное", "Учеба"]),
    ]
)]
final class EditCardRequest extends AbstractInput implements RulesProviderInterface
{
    #[RouteArgument('id')]
    private readonly string $id;

    #[Body('title')]
    #[Validate(new Required())]
    private string $title = '';

    #[Body('description')]
    #[Validate(new Required())]
    private string $description = '';


    #[Body('status')]
    private ?string $status = null;
    #[Body('priority')]
    private ?string $priority = null;

    #[Body('tags')]
    #[Validate(
        new Each(
            [new StringType()],
            skipOnEmpty: true
        )
    )]
    private array $tags = [];

    public function getTags(): array
    {
        return $this->tags;
    }
    public function getPriority(): string
    {
        return $this->priority;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getId(): string
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getRules(): array
    {
        return [
            'title' => [
                new Length(min: 5, max: 255),
            ],
            'description' => [
                new Length(min: 5, max: 1000),
            ],
            'status' => [
                new Length(max: 100),
            ],
            'tags' => [
                new Each([
                    new Length(max: 100),
                ], skipOnEmpty: true),
            ],
        ];
    }
}
