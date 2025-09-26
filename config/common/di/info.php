<?php

declare(strict_types=1);

use App\AuthorProvider;

/** @var array $params */

return [
    AuthorProvider::class => [
        '__construct()' => [
            'author' => $params['app']['info']['author'],
        ],
    ],
];
