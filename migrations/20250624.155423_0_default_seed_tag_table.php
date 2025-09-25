<?php

declare(strict_types=1);

namespace App\Migration;

use Cycle\Migrations\Migration;

class OrmDefault6a5be9e29c80d1639910b2784eee820b extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        // Базовые теги для системы
        $tags = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'name' => 'Работа'
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'name' => 'Личное'
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440003',
                'name' => 'Учеба'
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440005',
                'name' => 'Покупки'
            ]
        ];
        $db = $this->database();

        foreach ($tags as $tag) {
            $db->execute(
                "INSERT INTO tag (id, name) VALUES (?, ?)",
                [$tag['id'], $tag['name']]
            );
        }
    }

    public function down(): void
    {
        $this->database()->execute('DELETE FROM tag');
    }
}
