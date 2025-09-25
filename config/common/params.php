<?php

declare(strict_types=1);

use App\Queue\LoggingAuthorizationHandler;
use Cycle\Database\Config\Postgres\TcpConnectionConfig;
use Cycle\Database\Config\PostgresDriverConfig;
use Cycle\Schema\Provider\PhpFileSchemaProvider;
use Cycle\Schema\Provider\SimpleCacheSchemaProvider;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Queue\Adapter\SynchronousAdapter;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AttributedSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;
use Yiisoft\Yii\Middleware\Locale;

return [
    'mailer' => [
        'adminEmail' => 'admin@example.com',
        'senderEmail' => 'sender@example.com',
    ],
    'locale' => [
        'locales' => ['en' => 'en-US', 'ru' => 'ru-RU', 'de' => 'de-DE'],
        'ignoredRequests' => [
            '/gii**',
            '/debug**',
            '/inspect**',
        ],
    ],
    'supportEmail' => 'support@example.com',
    'middlewares' => [
        ErrorCatcher::class,
        Locale::class,
        Router::class,
    ],

    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__, 2),
            '@assets' => '@public/assets',
            '@assetsUrl' => '@baseUrl/assets',
            '@baseUrl' => '',
            '@data' => '@root/data',
            '@messages' => '@resources/messages',
            '@public' => '@root/public',
            '@resources' => '@root/resources',
            '@runtime' => '@root/runtime',
            '@src' => '@root/src',
            '@tests' => '@root/tests',
            '@views' => '@root/views',
            '@vendor' => '@root/vendor',
        ],
    ],

    'yiisoft/router-fastroute' => [
        'enableCache' => false,
    ],

    'yiisoft/translator' => [
        'locale' => 'en',
        'fallbackLocale' => 'en',
        'defaultCategory' => 'app',
    ],

    'yiisoft/yii-cycle' => [
        // DBAL config
        'dbal' => [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            // For example, \Yiisoft\Yii\Cycle\Logger\StdoutQueryLogger::class
            'query-logger' => null,
            // Default database
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => [
                    'driver' => 'postgres',
                    'connection' => 'postgres',
                ],
            ],
            'connections' => [
                'postgres' => new PostgresDriverConfig(
                    connection: new TcpConnectionConfig(
                        database: $_ENV['DB_NAME'],
                        host: $_ENV['DB_HOST'],
                        port: $_ENV['DB_PORT'],
                        user: $_ENV['DB_USERNAME'],
                        password: $_ENV['DB_PASSWORD'],
                    ),
                    queryCache: true
                ),
            ],
        ],

        // Cycle migration config
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * SchemaProvider list for {@see SchemaProviderPipeline}
         * Array of classname and {@see SchemaProviderInterface} object.
         * You can configure providers if you pass classname as key and parameters as array:
         * [
         *     SimpleCacheSchemaProvider::class => [
         *         'key' => 'my-custom-cache-key'
         *     ],
         *     FromFilesSchemaProvider::class => [
         *         'files' => ['@runtime/cycle-schema.php']
         *     ],
         *     FromConveyorSchemaProvider::class => [
         *         'generators' => [
         *              Generator\SyncTables::class, // sync table changes to database
         *          ]
         *     ],
         * ].
         */
        'schema-providers' => [
            // Uncomment next line to enable a Schema caching in the common cache
            // \Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class => ['key' => 'cycle-orm-cache-key'],
//            FromFilesSchemaProvider::class => ['files' => ['@src/schema.php']],
            SimpleCacheSchemaProvider::class => SimpleCacheSchemaProvider::config(
                key: 'cycle-schema'
            ),
            // Чтение/запись схемы в файл
            PhpFileSchemaProvider::class => PhpFileSchemaProvider::config(
                '@runtime/schema.php'
            ),
            // Генерация схемы из атрибутов ваших сущностей
            FromConveyorSchemaProvider::class => [
                'generators' => [
                    //SyncTables::class, // sync table changes to database
                ],
            ],
        ],

        /**
         * Config for {@see AnnotatedSchemaConveyor}
         * Annotated entity directories list.
         * {@see Aliases} are also supported.
         */
        'entity-paths' => [
            '@src',
        ],
    ],
    'yiisoft/yii-swagger' => [
        'annotation-paths' => [
            '@src',
        ],
    ],

    'yiisoft/queue' => [
        'handlers' => [
            LoggingAuthorizationHandler::NAME => [LoggingAuthorizationHandler::class, 'handle'],
        ],
        'channel-definitions' => [
            LoggingAuthorizationHandler::CHANNEL => SynchronousAdapter::class,
        ],
    ],

    'yiisoft/yii-debug-api' => [
        'allowedIPs' => ['172.0.0.1/10'],
    ],
];
