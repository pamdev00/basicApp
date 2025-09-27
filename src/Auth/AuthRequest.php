<?php

declare(strict_types=1);

namespace App\Auth;

use OpenApi\Attributes as OA;
use Yiisoft\Hydrator\Validator\Attribute\Validate;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Input\Http\RequestInputInterface;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

#[OA\Schema(
    schema: 'AuthRequest',
    properties: [
        new OA\Property(property: 'login', type: 'string', example: 'Opal1144'),
        new OA\Property(property: 'password', type: 'string', example: 'Opal1144'),
    ]
)]
final class AuthRequest implements RequestInputInterface, RulesProviderInterface
{
    #[Body('login')]
    #[Validate(
        new Required(),
        new Length(min: 8),
    )]
    private string $login = '';

    #[Body('password')]
    private string $password = '';

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRules(): array
    {
        return [
            'login' => [
                new Required(),
                new Length(min: 8),
            ],
            'password' => [
                new Required(),
            ],
        ];
    }
}
