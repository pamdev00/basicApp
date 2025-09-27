<?php

declare(strict_types=1);

namespace Acceptance\Card;

use App\Tests\Support\AcceptanceTester;
use App\Tests\Support\Helper\CardHelper;
use App\Tests\Support\Helper\ChecklistHelper;
use Codeception\Util\HttpCode;
use JsonException;
use PHPUnit\Framework\Assert;

final class ChecklistCest
{
    public function getChecklistList(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $I->sendGET('api/cards/' . $cardId . '/checklists');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);
    }

    public function createChecklist(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);
        $checklistId = ChecklistHelper::createChecklist($I, $cardId, [
            'title' => 'My Checklist',
        ]);

        Assert::assertNotEmpty($checklistId);

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);

        $I->seeInDatabase('checklist', [
            'title' => 'My Checklist',
            'card_id' => $cardId,
        ]);
    }

    public function updateChecklist(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);
        $checklistId = ChecklistHelper::createChecklist($I, $cardId);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('api/checklists/' . $checklistId, [
            'title' => 'Updated Checklist',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);

        $I->seeInDatabase('checklist', [
            'id' => $checklistId,
            'title' => 'Updated Checklist',
        ]);
    }

    public function deleteChecklist(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);
        $checklistId = ChecklistHelper::createChecklist($I, $cardId);

        $I->sendDELETE('api/checklists/' . $checklistId);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $deletedAt = $I->grabFromDatabase('checklist', 'deleted_at', [
            'id' => $checklistId,
        ]);
        Assert::assertNotEmpty($deletedAt);

        $I->sendDELETE('api/checklists/' . $checklistId);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * @throws JsonException
     */
    public function createChecklistWithEmptyTitle(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("api/cards/{$cardId}/checklists", [
            'title' => '',
        ]);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'detail' => 'One or more validation errors occurred.',
            'errors' => [
                'title' => ['Title cannot be blank.'],
            ],
        ]);
    }

    /**
     * @throws JsonException
     */
    public function createChecklistWithTooLongTitle(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("api/cards/{$cardId}/checklists", [
            'title' => str_repeat('A', 1024),
        ]);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'detail' => 'One or more validation errors occurred.',
            'errors' => [
                'title' => ['Title must contain at most 255 characters.'],
            ],
        ]);
    }

    /**
     * @throws JsonException
     */
    public function createChecklistWithSqlInjection(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $payload = [
            'title' => "xxx'); DROP TABLE checklist;--",
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("api/cards/{$cardId}/checklists", $payload);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
            'data' => [
                'title' => $payload['title'],
            ],
        ]);

        $I->seeInDatabase('checklist', [
            'title' => $payload['title'],
        ]);
    }
}
