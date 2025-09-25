<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefault6909a41948271a80244124ba41eea33f extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {

        $this->table('checklist_item')
            ->addColumn('created_at', 'datetime', [
                'nullable' => false,
                'defaultValue' => 'CURRENT_TIMESTAMP',
                'withTimezone' => false,
            ])
            ->addColumn('updated_at', 'datetime', ['nullable' => false, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('deleted_at', 'datetime', ['nullable' => true, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('description', 'text', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('is_completed', 'boolean', ['nullable' => false, 'defaultValue' => 'false'])
            ->addColumn('checklist_id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addIndex(['checklist_id'], ['name' => 'checklist_item_index_checklist_id_68590cbd59b40', 'unique' => false])
            ->addForeignKey(['checklist_id'], 'checklist', ['id'], [
                'name' => 'checklist_item_foreign_checklist_id_68590cbd59b4f',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'indexCreate' => true,
            ])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('checklist_item')->drop();
    }
}
