<?php

declare(strict_types=1);

namespace App\Blog;

use MyCLabs\Enum\Enum;

enum PostStatus : int
{
    case PUBLIC = 0;
    case DRAFT = 1;
    case DELETED = 2;
}
