<?php

declare(strict_types=1);

namespace App\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'login', type: 'string', example: 'UserName'),
        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'status', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', example: '13.12.2020 00:04:20'),
    ]
)]
final class UserFormatter
{
    public function format(User $user): array
    {
        return [
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'status' => $user->getStatus()->value,
            'created_at' => $user
                ->getCreatedAt()
                ->format('d.m.Y H:i:s'),
        ];
    }
}
