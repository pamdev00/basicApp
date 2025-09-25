<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

final class DocsCest
{
    public function testErrorPage(AcceptanceTester $I): void
    {
        $I->sendGET('/docs/errors/application-error');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains('Error Details');
        $I->seeResponseContains('application-error');
    }
}