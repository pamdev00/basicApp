<?php

declare(strict_types=1);

/**
 * @var WebView $this
 * @var Message $message
 * @var string $content
 */

use Yiisoft\Mailer\Message;
use Yiisoft\View\WebView;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= $this->getTitle() ?></title>
</head>
<body>
    <?= $content ?>
    <p>This is a layout.</p>
</body>
</html>

