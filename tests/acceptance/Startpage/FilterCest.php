<?php

namespace App\Test\Startpage;

use App\Test\AcceptanceTester;

class FilterCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage("/news");
        $I->waitForElement('body > main > div > ul > li:nth-child(2) > a');
    }

    /**
     * @skip
     */
    public function firstFilterWillReduceElements(AcceptanceTester $I)
    {
        $actualItems = $I->executeJS("var count = 0; var a = document.getElementsByClassName('video-container'); for(i=0;i<a.length;i++) {if (a[i].parentElement.style.display == '') count++};return count;");
        $I->click('body > main > div > ul > li:nth-child(2) > a');
        $I->wait(2); //TODO refactor, später!
        $filteredItems = $I->executeJS("var count = 0; var a = document.getElementsByClassName('video-container'); for(i=0;i<a.length;i++) {if (a[i].parentElement.style.display == '') count++};return count;");
        $I->assertLessThan($filteredItems, $actualItems);
    }
}
