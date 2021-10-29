<?php
namespace App\Test\Startpage;
use App\Test\AcceptanceTester;

class NavigationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/news');
        $I->waitForElement('#navbarCollapse > ul');
    }

    public function videosWillRedirectToVideoPage(AcceptanceTester $I)
    {
        $I->click('Videos');
        $I->seeInCurrentUrl('/videos');
    }
}
