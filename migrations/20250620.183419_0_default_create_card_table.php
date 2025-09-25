<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefaultC51fded2d470770248b50b9d32dc4fac extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('card')
            ->addColumn('created_at', 'datetime', [
                'nullable' => false,
                'defaultValue' => 'CURRENT_TIMESTAMP',
                'withTimezone' => false,
            ])
            ->addColumn('updated_at', 'datetime', ['nullable' => false, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('deleted_at', 'datetime', ['nullable' => true, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('title', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
            ->addColumn('description', 'text', ['nullable' => true, 'defaultValue' => null])
            ->addColumn('status', 'string', ['nullable' => false, 'defaultValue' => 'todo', 'size' => 50])
            ->addColumn('priority', 'string', ['nullable' => false, 'defaultValue' => 'medium', 'size' => 50])
            ->addColumn('due_date', 'datetime', ['nullable' => true, 'defaultValue' => null, 'withTimezone' => false])
            ->addIndex(['status', 'priority', 'due_date'], [
                'name' => 'card_index_status_priority_due_date',
                'unique' => false,
            ])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('card')->drop();
    }
}
