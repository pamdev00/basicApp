<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;
use JsonException;

final class ChecklistItemHelper
{
    /**
     * @throws JsonException
     */
    public static function createItem(AcceptanceTester $I, string $checklistId, array $data = []): string
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/checklists/' . $checklistId . '/items', array_merge([
            'description' => 'Checklist Item',
            'is_completed' => false,
        ], $data));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();

        $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);

        return $response['data']['id'] ?? '';
    }
}
