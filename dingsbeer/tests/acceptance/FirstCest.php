<?php

class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }

    public function frontPageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Search Beer Reviews');
    }

    public function SingleQuotesWorkInDropdownSearch(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->selectOption('dbb_brewery', 'Federal Jack\'s Brewpub');
        $I->click(['id' => 'submit']);
        $I->see('Results:');
        $I->see('Kennebunkport Pumpkin Ale');
        $I->see('Blueberry Wheat Ale');
        $I->dontSee('Rosey Nosey');
    }

    public function DoubleQuotesWorkInDropdownSearch(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->selectOption('dbb_brewery', urlencode('"DoubleQuote Brewery"'));
        $I->click(['id' => 'submit']);
        $I->see('Results');
        $I->see('DoubleQuote Ale');
        $I->dontSee('Rosey Nosey');
    }
}
