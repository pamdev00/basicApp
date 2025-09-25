<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefault34d964b0bd196a5fd53a47fe48dfe19d extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('email_verification_token')
            ->addColumn('id', 'primary')
            ->addColumn('token_hash', 'string', ['nullable' => false])
            ->addColumn('expires_at', 'datetime', ['nullable' => false])
            ->addColumn('used_at', 'datetime', ['nullable' => true, 'defaultValue' => null])
            ->addColumn('created_at', 'datetime', ['nullable' => false])
            ->addColumn('user_id', 'integer', ['nullable' => false])
            ->addIndex(['user_id'], ['name' => 'email_verification_tokens_index_user_id'])
            ->addForeignKey(['user_id'], 'user', ['id'], [
                'name' => 'email_verification_token_foreign_user_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('email_verification_token')->drop();
    }
}
