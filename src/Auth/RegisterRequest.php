<?php

declare(strict_types=1);

namespace App\Auth;

use OpenApi\Attributes as OA;
use Yiisoft\Hydrator\Validator\Attribute\Validate;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Validator\Rule\CompareType;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Equal;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

#[OA\Schema(
    schema: 'RegisterRequest',
    properties: [
        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'password', type: 'string', example: 'Str0ng!Passw0rd'),
        new OA\Property(property: 'password_confirm', type: 'string', example: 'Str0ng!Passw0rd'),
        new OA\Property(property: 'login', type: 'string', example: 'JohnDoe'),
    ]
)]
#[FromBody]
final class RegisterRequest extends AbstractInput
{
    #[Body('email')]
    #[Validate(
        new Required(),
        new Email(),
        new Length(min: 5, max: 100),
    )]
    private string $email = '';

    #[Body('password')]
    #[Validate(
        new Required(),
        new Length(min: 8, max: 100),
    )]
    private string $password = '';

    #[Body('password_confirm')]
    #[Required]
    #[Equal(targetProperty: 'password', type: CompareType::STRING)]
    private string $passwordConfirm = '';

    #[Body('login')]
    #[Validate(
        new Required()
    )]
    private string $login = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

}
