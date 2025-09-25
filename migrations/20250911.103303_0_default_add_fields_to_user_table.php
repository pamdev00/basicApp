<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefault8c17a45723176992302c33b05e65c930 extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('user')
            ->addColumn('email', 'string', ['nullable' => false, 'size' => 100])
            ->addColumn('status', 'integer', ['nullable' => false, 'defaultValue' => 1])
            ->addColumn('email_verified_at', 'datetime', ['nullable' => true, 'defaultValue' => null])
            ->addIndex(['email'], ['name' => 'user_index_email',
                'expression' => 'LOWER(email)',
                'unique' => true])
            ->update();
    }

    public function down(): void
    {
        $this->table('user')
            ->dropIndex(['email'])
            ->dropColumn('email_verified_at')
            ->dropColumn('status')
            ->dropColumn('email')
            ->update();
    }
}
