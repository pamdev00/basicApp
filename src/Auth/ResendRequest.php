<?php

declare(strict_types=1);

namespace App\Auth;

use OpenApi\Attributes as OA;
use Yiisoft\Hydrator\Validator\Attribute\Validate;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Required;

#[OA\Schema(schema: 'ResendRequest', properties: [
    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
])]
#[FromBody]
final class ResendRequest extends AbstractInput
{
    #[Body('email')]
    #[Validate(new Required(), new Email())]
    private string $email = '';

    public function getEmail(): string
    {
        return $this->email;
    }
}
