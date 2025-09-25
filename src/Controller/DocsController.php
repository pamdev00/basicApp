<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class DocsController
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withControllerName('docs')
            ->withViewPath('@views');
    }

    public function errorPage(#[RouteArgument('type')] string $type): ResponseInterface
    {
        return $this->viewRenderer->render('error', ['type' => $type]);
    }
}
