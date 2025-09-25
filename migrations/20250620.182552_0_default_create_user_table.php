<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefaultC9add0a5848c2a9864a4d388569ecedb extends Migration
{
    protected const string DATABASE = 'default';

            public function up(): void
    {
        $this->table('user')
            ->addColumn('created_at', 'datetime', [
                'nullable' => false,
                'defaultValue' => 'CURRENT_TIMESTAMP',
                'withTimezone' => false,
            ])
            ->addColumn('updated_at', 'datetime', ['nullable' => false, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('id', 'primary', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('token', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 128])
            ->addColumn('login', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 48])
            ->addColumn('password_hash', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
            ->addIndex(['login', 'token'], ['name' => 'user_index_login_token', 'unique' => true])
            ->setPrimaryKeys(['id'])
            ->create();
        $this->table('post')
            ->addColumn('id', 'primary', ['nullable' => false, 'defaultValue' => null])
            ->addColumn('slug', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 128])
            ->addColumn('title', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 255])
            ->addColumn('status', 'integer', ['nullable' => false, 'defaultValue' => 0])
            ->addColumn('content', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
            ->addColumn('created_at', 'datetime', ['nullable' => false, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('updated_at', 'datetime', ['nullable' => false, 'defaultValue' => null, 'withTimezone' => false])
            ->addColumn('user_id', 'integer', ['nullable' => false, 'defaultValue' => null])
            ->addIndex(['user_id'], ['name' => 'post_index_user_id', 'unique' => false])
            ->addForeignKey(['user_id'], 'user', ['id'], [
                'name' => 'post_foreign_user_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'indexCreate' => true,
            ])
            ->setPrimaryKeys(['id'])
            ->create();



    }
    public function down(): void
    {
        $this->table('post')->drop();
        $this->table('user')->drop();
    }
}
