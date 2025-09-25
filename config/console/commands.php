<?php

declare(strict_types=1);

use App\Auth\Console\TokenCleanupCommand;
use App\Cards\Card\Console\TestCommand;
use Yiisoft\Yii\Cycle\Command\Migration;
use Yiisoft\Yii\Cycle\Command\Schema;

return [
    'token:cleanup' => TokenCleanupCommand::class,
    'cycle/schema' => Schema\SchemaCommand::class,
    'cycle/schema/php' => Schema\SchemaPhpCommand::class,
    'cycle/schema/clear' => Schema\SchemaClearCommand::class,
    'cycle/schema/rebuild' => Schema\SchemaRebuildCommand::class,
    'migrate/create' => Migration\CreateCommand::class,
    'migrate/generate' => Migration\GenerateCommand::class,
    'migrate/up' => Migration\UpCommand::class,
    'migrate/down' => Migration\DownCommand::class,
    'migrate/list' => Migration\ListCommand::class,
    'test/up' => TestCommand::class,
];
