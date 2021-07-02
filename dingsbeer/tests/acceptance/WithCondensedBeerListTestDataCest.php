<?php

# These tests should be run once the following test CSV file has been uploaded
# from the WordPress admin Ding's Beer Blog plugin:
#
# dingsbeer/dingsbeer/test/condensed_beer_list_testcase.utf8.csv
#
# The file contains about 80+ beers, reviewed over several years, in various formats
# and including some special characters in the 


class WithCondensedBeerListTestDataCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
    }

    // tests
    public function searchPageExists(AcceptanceTester $I)
    {
        $I->see('Search Beer Reviews');
        $I->seeElement('#dbb_search_form');
    }

    public function searchResultsWithNoTerms(AcceptanceTester $I)
    {
        $I->click(['id' => 'submit']);
        $I->expectTo('see all beers returned as paginated results');
        $I->seeElement('#dbb_search_results');
        $I->see('Results:');
        $I->see('DoubleQuote Ale');
        $I->see('DoubleQuote Beer');
        $I->see('Bitter American');
        $I->seeElement('.pagination');
    }

    public function searchByBeerNameYieldsResult(AcceptanceTester $I) {
        $I->fillField('dbb_beer_name', 'Sancti');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->see('Sanctification');
    }

    public function searchForNonExistentBeerNameYieldsNoResult(AcceptanceTester $I)
    {
        $I->fillField('dbb_beer_name', 'Sanctification FOOBAR'); # a beer name I know doesn't exist in the test data
        $I->click(['id' => 'submit']);
        $I->dontSeeElement('#dbb_search_results');
        $I->see('Sorry, no posts matched your criteria.');
    }

    public function searchForGarbageNotesValueYieldsNoResult(AcceptanceTester $I)
    {
        $I->fillField('dbb_notes', 'lakdjflakdjflsdkfjldkfj');
        $I->click(['id' => 'submit']);
        $I->dontSeeElement('#dbb_search_results');
        $I->see('Sorry, no posts matched your criteria.');
    }

    public function searchForBreweryWithMultipleResults(AcceptanceTester $I)
    {
        $I->selectOption('dbb_brewery', '21st Amendment Brewery');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->see('Bitter American');
        $I->see('Monk');
        $I->see('Fireside Chat');
        $I->see('Hop Crisis');
        $I->dontSee('Sorry, no posts matched your criteria.');
    }

    public function searchForBreweryWithSingleResult(AcceptanceTester $I)
    {
        $I->selectOption('dbb_brewery', 'Boddingtons');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->see('Boddingtons Pub Ale');
        $I->dontSee('Sorry, no posts matched your criteria.');
    }

    public function searchForBreweryWithUTF8Chars(AcceptanceTester $I)
    {
        $I->selectOption('dbb_brewery', '%C5%BDateck%C3%BD+Pivovar');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->dontSee('Sorry, no posts matched your criteria.');
        $I->see('Žatec');
    }

    public function searchByNotesFieldYieldsResults(AcceptanceTester $I)
    {
        $I->fillField('dbb_notes', 'even produces a little lace and retention');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->dontSee('Sorry, no posts matched your criteria.');
        $I->see('Mission St. Hefeweizen');
    }

    /**
     * @dataprovider numericSearchFieldProvider
     */
    public function numericSearchFieldsValidate(AcceptanceTester $I, \Codeception\Example $example)
    {
        $I->fillField('dbb_' . $example['field'], $example['term']);
        $I->click(['id' => 'submit']);
        $I->see($example['result']);
    }

    /**
     *  @return array
     */
    protected function numericSearchFieldProvider()
    {
        $numeric_fields = ['year', 'abv', 'appearance', 'smell', 'mouthfeel', 'taste', 'overall'];
        $test_data = [];
        foreach ($numeric_fields as $field) {
            array_push($test_data,  ['field' => $field, 'term' => 'foo', 'result' => 'Invalid search term']);
            array_push($test_data, ['field' => $field, 'term' => '0', 'result' => 'Results:']);
            array_push($test_data, ['field' => $field, 'term' => '3.0', 'result' => 'Results:']);
        }

        return $test_data;
    }


    

}
