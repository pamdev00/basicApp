<?php

declare(strict_types=1);

namespace App\Dto;

use JsonSerializable;
use Throwable;

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
