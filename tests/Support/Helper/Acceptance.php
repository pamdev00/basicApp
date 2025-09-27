<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

use Codeception\Module;
use Exception;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends Module
{
    public function getMailDirectoryPath(): string
    {
        return __DIR__ . '/../../../runtime/mail';
    }
    public function getRateLimiterCachePath(): string
    {
        return __DIR__ . '/../../../runtime/rate-limiter';
    }

    /**
     * @throws Exception
     */
    public function grabLatestEmailContent(string $mailDir): string
    {
        $files = glob(__DIR__ . '/../../../' . $mailDir . '/*.eml');
        if (!$files) {
            throw new Exception('No email files found.');
        }

        $latestFile = '';
        $latestTime = 0;

        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latestTime) {
                $latestTime = $mtime;
                $latestFile = $file;
            }
        }

        if (empty($latestFile)) {
            throw new Exception('Could not determine the latest email file.');
        }

        return file_get_contents($latestFile);
    }
}
