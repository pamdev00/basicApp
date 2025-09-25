<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

final class ChecklistHelper
{
    public static function createChecklist(AcceptanceTester $I, string $cardId, array $data = []): string
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/cards/' . $cardId . '/checklists', array_merge([
            'title' => 'Checklist Title',
        ], $data));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();

        $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);

        return $response['data']['id'] ?? '';
    }
}
