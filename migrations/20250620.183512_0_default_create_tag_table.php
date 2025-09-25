<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefaultF595d0befbdb8aadbee3475a9cb5dadf extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {

        $this->table('tag')
            ->addColumn('id', 'uuid', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('name', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 100])
            ->addIndex(['name'], ['name' => 'tag_index_name', 'unique' => true])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('tag')->drop();
    }
}
