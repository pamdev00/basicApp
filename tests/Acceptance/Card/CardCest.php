<?php

namespace Acceptance\Card;

use App\Tests\Support\AcceptanceTester;
use App\Tests\Support\Helper\CardHelper;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

final class CardCest
{
    public function getCardList(AcceptanceTester $I): void
    {
        $I->sendGET('api/cards?page=1');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);
    }

    public function createCard(AcceptanceTester $I): void
    {

        $cardId = CardHelper::createCard($I, [
            'title' => 'My New Card',
        ]);

        Assert::assertNotEmpty($cardId);

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);

        $I->seeInDatabase('card', [
            'title' => 'My New Card',
            'status' => 'todo',
        ]);
    }

    public function createCardWithTags(AcceptanceTester $I): void
    {


        $cardId = CardHelper::createCard($I, [
            'title' => 'My New Card',
            'tags' => ['Учеба', 'Работа']
        ]);

        Assert::assertNotEmpty($cardId);

        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
            'data' => [
                'title' => 'My New Card',
                'description' => 'Test desc',
                'status' => 'todo',
                'priority' => 'medium',
                'tags' => ['Учеба', 'Работа']
            ]
        ]);

        $I->seeInDatabase('card', [
            'title' => 'My New Card',
            'status' => 'todo',
        ]);
    }


    public function getCardById(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $I->sendGET('api/cards/' . $cardId);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
        ]);

        $I->seeResponseContainsJson(['id' => $cardId]);
    }

    public function updateCard(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('api/cards/' . $cardId, [
            'title' => 'Updated Card',
            'description' => 'Updated desc',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
            'data' => [
                'id' => $cardId,
                'title' => 'Updated Card',
            ],
        ]);

        $I->seeInDatabase('card', [
            'title' => 'Updated Card',
            'status' => 'todo',
        ]);
    }

    public function deleteCard(AcceptanceTester $I): void
    {
        $cardId = CardHelper::createCard($I);

        $I->sendDELETE('api/cards/' . $cardId);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        // Проверяем soft-delete (deleted_at не null)
        $deletedAt = $I->grabFromDatabase('card', 'deleted_at', [
            'id' => $cardId,
        ]);

        Assert::assertNotEmpty($deletedAt);

        // Повторное удаление — 404
        $I->sendDELETE('api/cards/' . $cardId);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function notFoundCard(AcceptanceTester $I): void
    {
        $I->sendGET('api/cards/00000000-0000-0000-0000-000000000000');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function createInvalidCard(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/cards', [
            'title' => '',
            'description' => '',
        ]);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'errors' => [
                'title' => ['Title cannot be blank.'],
                'description' => ['Description cannot be blank.']
            ]
        ]);
    }

    public function createCardWithEmptyTitle(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/cards',[
            'title' => '',
        ]);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'errors' => [
                'title' => ['Title cannot be blank.'],
                'description' => ['Description not passed.'],
            ]
        ]);
    }

    public function createCardWithTooLongTitle(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/cards',[
            'title' => str_repeat('A', 5024),
            'description' => str_repeat('A', 800)
        ]);


        $I->seeResponseCodeIs(422);
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => 422,
            'errors' => [
                'title' => ['Title must contain at most 255 characters.'],
                'status' => ['Status must be a string. null given.'],
            ]
        ]);
    }

    public function createCardWithSqlInjection(AcceptanceTester $I): void
    {
        $payload = [
            'title' => "xxx'); DROP TABLE card;--",
            'description' => "xxx'); DROP TABLE card;--",
            'status' => 'todo',
            'priority' => 'medium',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('api/cards', $payload);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson([
            'status' => 'success',
            'error_message' => "",
            'error_code' => null,
            'data' => [
                'title' => $payload['title'],
            ],
        ]);

        $I->seeInDatabase('card', [
            'title' => $payload['title'],
            'status' => 'todo',
        ]);
    }

}
