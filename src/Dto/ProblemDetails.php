<?php

declare(strict_types=1);

namespace App\Dto;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProblemDetails',
    type: 'object',
    title: 'Problem Details',
    description: 'A standard format for specifying errors in HTTP API responses based on RFC 7807.',
    properties: [
        new OA\Property(property: 'type', type: 'string', format: 'uri', description: 'A URI reference that identifies the problem type.', example: '/docs/errors/validation-error'),
        new OA\Property(property: 'title', type: 'string', description: 'A short, human-readable summary of the problem type.', example: 'Validation Failed'),
        new OA\Property(property: 'status', type: 'integer', format: 'int32', description: 'The HTTP status code.', example: 422),
        new OA\Property(property: 'detail', type: 'string', description: 'A human-readable explanation specific to this occurrence of the problem.', example: 'One or more validation errors occurred.'),
        new OA\Property(property: 'instance', type: 'string', format: 'uri', description: 'A URI reference that identifies the specific occurrence of the problem.', example: '/auth/register'),
    ]
)]
final class ProblemDetails implements JsonSerializable
{
    private array $data = [];

    public function __construct(
        private readonly string $type,
        private readonly string $title,
        private readonly int $status,
        private readonly ?string $detail = null,
        private readonly ?string $instance = null,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function withData(array $data): self
    {
        $new = clone $this;
        $new->data = $data;
        return $new;
    }

    public function jsonSerialize(): array
    {
        $result = [
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
            'detail' => $this->detail,
            'instance' => $this->instance,
        ];

        return array_merge($result, $this->data);
    }
}
