<?php

declare(strict_types=1);

namespace App\Controller;

use Yiisoft\DataResponse\DataResponse;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class FrontController
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withControllerName('front')
            ->withViewPath('@views');
    }

    public function index(

    ): DataResponse
    {


        return $this->viewRenderer->render('index', ['name' => 'vova']);
    }
}
