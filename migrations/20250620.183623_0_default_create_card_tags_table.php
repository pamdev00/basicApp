<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefaultFf174cf4b43529e5bc79fa4974a5db18 extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {

        $this->table('card_tag')
            ->addColumn('id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('card_id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('tag_id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addIndex(['card_id'], ['name' => 'card_tag_index_card_id', 'unique' => false])
            ->addIndex(['tag_id'], ['name' => 'card_tag_index_tag_id', 'unique' => false])
            ->addIndex(['tag_id', 'card_id'], ['name' => 'card_tag_index_tag_id_card_id', 'unique' => true])
            ->addForeignKey(['card_id'], 'card', ['id'], [
                'name' => 'card_tag_foreign_card_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'indexCreate' => true,
            ])
            ->addForeignKey(['tag_id'], 'tag', ['id'], [
                'name' => 'card_tag_foreign_tag_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'indexCreate' => true,
            ])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('card_tag')->drop();
    }
}
