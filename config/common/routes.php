<?php

declare(strict_types=1);

use App\Auth\AuthController;
use App\Blog\BlogController;
use App\Cards\Card\Controller\CardController;
use App\Cards\Card\Controller\ChecklistController;
use App\Cards\Card\Controller\ChecklistItemController;
use App\Controller\DocsController;
use App\Factory\RestGroupFactory;
use App\InfoController;
use App\User\UserController;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\RequestProvider\RequestCatcherMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Swagger\Action\SwaggerJson;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Yii\Middleware\CorsAllowAll;
use Yiisoft\Yii\RateLimiter\LimitRequestsMiddleware;

return [
    Route::get('/')
        ->action([InfoController::class, 'index'])
        ->name('api/info'),

    Route::get('/blog/')
        ->middleware(RequestCatcherMiddleware::class)
        ->action([BlogController::class, 'index'])
        ->name('blog/index'),

    Route::get('/blog/{id:\d+}')
        ->action([BlogController::class, 'view'])
        ->name('blog/view'),

    Route::post('/blog/')
        ->middleware(Authentication::class)
        ->middleware(RequestCatcherMiddleware::class)
        ->action([BlogController::class, 'create'])
        ->name('blog/create'),

    Route::put('/blog/{id:\d+}')
        ->middleware(Authentication::class)
        ->middleware(RequestCatcherMiddleware::class)
        ->action([BlogController::class, 'update'])
        ->name('blog/update'),

    RestGroupFactory::create('/users/', UserController::class)
        ->prependMiddleware(Authentication::class),

    Route::post('/auth/')
        ->middleware(RequestCatcherMiddleware::class)
        ->action([AuthController::class, 'login'])
        ->name('auth'),

    Route::post('/register/')
        ->middleware(LimitRequestsMiddleware::class)
        ->middleware(RequestCatcherMiddleware::class)
        ->action([AuthController::class, 'register'])
        ->name('register'),

    Route::get('/verify-email/{token}')
        ->middleware(RequestCatcherMiddleware::class)
        ->name('auth/verify-email')
        ->action([AuthController::class, 'verifyEmail']),

    Route::post('/resend-verification')
        ->middleware(LimitRequestsMiddleware::class)
        ->middleware(RequestCatcherMiddleware::class)
        ->action([AuthController::class, 'resendVerification'])
        ->name('auth/resend-verification'),

    Route::post('/logout/')
        ->middleware(Authentication::class)
        ->middleware(RequestCatcherMiddleware::class)
        ->action([AuthController::class, 'logout'])
        ->name('logout'),

    Group::create('/api')
        ->routes(
            Route::get('/cards')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([CardController::class, 'index'])
                ->name('card/index'),

            Route::get('/cards/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([CardController::class, 'view'])
                ->name('card/view'),

            Route::put('/cards/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([CardController::class, 'update'])
                ->name('card/update'),

            Route::delete('/cards/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([CardController::class, 'delete'])
                ->name('card/delete'),

            Route::post('/cards')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([CardController::class, 'create'])
                ->name('card/create'),


            Route::get('/cards/{cardId}/checklists')
                ->action([ChecklistController::class, 'index'])
                ->name('checklists/index'),

            Route::post('/cards/{cardId}/checklists')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([ChecklistController::class, 'create'])
                ->name('checklists/create'),

            // Direct checklist routes
            Route::get('/checklists/{id:[a-f0-9\-]{36}}')
                ->action([ChecklistController::class, 'view'])
                ->name('checklists/view'),

            Route::put('/checklists/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([ChecklistController::class, 'update'])
                ->name('checklists/update'),

            Route::delete('/checklists/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([ChecklistController::class, 'delete'])
                ->name('checklists/delete'),

            // Checklist item routes
            Route::post('/checklists/{id:[a-f0-9\-]{36}}/items')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([ChecklistItemController::class, 'create'])
                ->name('checklist-items/create'),

            Route::put('/checklist-items/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([ChecklistItemController::class, 'update'])
                ->name('checklist-items/update'),

            Route::patch('/checklist-items/{id:[a-f0-9\-]{36}}/toggle')
                ->action([ChecklistItemController::class, 'toggle'])
                ->name('checklist-items/toggle'),

            Route::delete('/checklist-items/{id:[a-f0-9\-]{36}}')
                ->middleware(RequestCatcherMiddleware::class)
                ->action([ChecklistItemController::class, 'delete'])
                ->name('checklist-items/delete'),
        ),

    Group::create('/front')
        ->routes(
            Route::get('')
                ->middleware(FormatDataResponseAsHtml::class)
                ->action([\App\Controller\FrontController::class, 'index'])
                ->name('front/index'),
        ),
    // Swagger routes
    Group::create('/docs')
        ->routes(
            Route::get('')
                ->middleware(FormatDataResponseAsHtml::class)
                ->action(static function (SwaggerUi $swaggerUi, UrlGeneratorInterface $urlGenerator) {
                    return $swaggerUi->withJsonUrl($urlGenerator->getUriPrefix() . '/docs/openapi.json');
                })
                ->name('swagger/index'),
            Route::get('/openapi.json')
                ->middleware(FormatDataResponseAsJson::class)
                ->middleware(CorsAllowAll::class)
                ->action(SwaggerJson::class),
            Route::get('/errors/{type:[a-zA-Z0-9-]+}')
                ->middleware(FormatDataResponseAsHtml::class)
                ->action([DocsController::class, 'errorPage'])
                ->name('docs/error-page'),
        ),
];
