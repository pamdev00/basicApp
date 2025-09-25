<?php

declare(strict_types=1);

namespace App;

use FilesystemIterator as FSIterator;
use RecursiveDirectoryIterator as DirIterator;
use RecursiveIteratorIterator as RIterator;

final class Installer
{
    /**
     * @psalm-suppress UndefinedClass
     */
    public static function postUpdate(): void
    {
        self::chmodRecursive('runtime');
        self::chmodRecursive('public/assets');
    }

    private static function chmodRecursive(string $path): void
    {
        chmod($path, 0777);
        $iterator = new RIterator(
            new DirIterator($path, FSIterator::SKIP_DOTS | FSIterator::CURRENT_AS_PATHNAME),
            RIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            chmod($item, 0777);
        }
    }

    public static function copyEnvFile(): void
    {
        if (!file_exists('.env')) {
            copy('.env.example', '.env');
        }
    }
}
