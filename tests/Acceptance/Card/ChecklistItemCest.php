<?php

declare(strict_types=1);

namespace Acceptance\Card;


use App\Tests\Support\AcceptanceTester;
use App\Tests\Support\Helper\CardHelper;
use App\Tests\Support\Helper\ChecklistHelper;
use App\Tests\Support\Helper\ChecklistItemHelper;
use Codeception\Util\HttpCode;
use JsonException;
use PHPUnit\Framework\Assert;

final class ChecklistItemCest
{
    /**
     * @throws JsonException
     */
    public function createItem(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);
        $checklistId = ChecklistHelper::createChecklist($I, $cardId);
        $itemId = ChecklistItemHelper::createItem($I, $checklistId, [
            'description' => 'Test Item',
            'is_completed' => false,
        ]);

        Assert::assertNotEmpty($itemId);

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);

        $I->seeInDatabase('checklist_item', [
            'id' => $itemId,
            'description' => 'Test Item',
            'checklist_id' => $checklistId,
        ]);
    }

    /**
     * @throws JsonException
     */
    public function toggleItem(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);
        $checklistId = ChecklistHelper::createChecklist($I, $cardId);
        $itemId = ChecklistItemHelper::createItem($I, $checklistId);

        $I->sendPATCH('api/checklist-items/' . $itemId . '/toggle');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);
    }

    /**
     * @throws JsonException
     */
    public function deleteItem(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);
        $checklistId = ChecklistHelper::createChecklist($I, $cardId);
        $itemId = ChecklistItemHelper::createItem($I, $checklistId);

        $I->sendDELETE('api/checklist-items/' . $itemId);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $deletedAt = $I->grabFromDatabase('checklist_item', 'deleted_at', [
            'id' => $itemId,
        ]);
        Assert::assertNotEmpty($deletedAt);

        $I->sendDELETE('api/checklist-items/' . $itemId);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
