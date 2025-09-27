<?php

declare(strict_types=1);

use Yiisoft\Validator\RuleHandlerResolver\RuleHandlerContainer;
use Yiisoft\Validator\RuleHandlerResolverInterface;

return [
    RuleHandlerResolverInterface::class => RuleHandlerContainer::class,
];
