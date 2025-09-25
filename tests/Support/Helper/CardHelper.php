<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;



use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

final class CardHelper
{
    public static function createCard(AcceptanceTester $I, array $data = []): string
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/cards', array_merge([
            'title' => 'Test Card',
            'description' => 'Test desc',
            'status' => 'todo',
            'priority' => 'medium',
            'tags' => []
        ], $data));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();

        $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);
        return $response['data']['id'] ?? '';
    }
}
