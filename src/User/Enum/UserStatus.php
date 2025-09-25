<?php
declare(strict_types=1);

namespace App\User\Enum;

enum UserStatus: int
{
    case WAITING_VERIFICATION = 1;
    case ACTIVE = 2;
}
