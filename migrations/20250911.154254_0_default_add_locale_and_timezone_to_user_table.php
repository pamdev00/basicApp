<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefaultEb33c744c53462fc92a2c2e679efa424 extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('user')
            ->addColumn('locale', 'string', ['nullable' => false, 'size' => 16, 'defaultValue' => 'en-US'])
            ->addColumn('timezone', 'string', ['nullable' => false, 'size' => 64, 'defaultValue' => 'UTC'])
            ->update();
    }

    public function down(): void
    {
        $this->table('user')
            ->dropColumn('timezone')
            ->dropColumn('locale')
            ->update();
    }
}
