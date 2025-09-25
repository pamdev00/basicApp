<?php

declare(strict_types=1);

namespace App\Cards\Card\Scope;

use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\ScopeInterface as ConstrainInterface;

final class PublicScope implements ConstrainInterface
{
    public function apply(QueryBuilder $query): void
    {
        // Показываем только неудаленные карточки
        $query->where('deleted_at', 'IS', null);
    }
}
