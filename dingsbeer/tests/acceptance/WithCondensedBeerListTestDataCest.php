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

    public function searchResultsWithNoTermsYieldsResults(AcceptanceTester $I)
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

    public function searchForGarbageBeerNameYieldsNoResult(AcceptanceTester $I)
    {
        $I->fillField('dbb_beer_name', 'lakdjflakdjflsdkfjldkfj');
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

    public function searchForGarbageNotesValueYieldsNoResult(AcceptanceTester $I)
    {
        $I->fillField('dbb_notes', 'lakdjflakdjflsdkfjldkfj');
        $I->click(['id' => 'submit']);
        $I->dontSeeElement('#dbb_search_results');
        $I->see('Sorry, no posts matched your criteria.');
    }


    /**
     * @dataprovider numericFieldNameProvider
     */
    public function textInNumericSearchFieldsFailsValidation(AcceptanceTester $I, \Codeception\Example $example)
    {
        $I->fillField('dbb_' . $example['field'], 'foo');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_validation_errors');
        $I->see('Invalid search terms. Please fix these problems.');
        $I->see(ucfirst($example['field']) . ' should be a number or left blank');
    }

     /**
     * @dataprovider numericFieldNameProvider
     */
    public function numericFieldGreaterThanEqualZeroYieldsResults(AcceptanceTester $I, \Codeception\Example $example)
    {
        $I->fillField('dbb_' . $example['field'], '0');
        $I->selectOption('dbb_' . $example['field'] . '_compare', 'greater_than_or_equal');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->seeElement('.pagination');
        $I->dontSee('Sorry, no posts matched your criteria');
    }

    /**
     * @dataprovider numericFieldNameProvider
     */
    public function numericFieldGreaterThanEqualZeroPointZeroYieldsResults(AcceptanceTester $I, \Codeception\Example $example)
    {
        $I->fillField('dbb_' . $example['field'], '0.0');
        $I->selectOption('dbb_' . $example['field'] . '_compare', 'greater_than_or_equal');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->seeElement('.pagination');      
        $I->dontSee('Sorry, no posts matched your criteria');  
    }

    /**
     * @dataprovider numericFieldNameProvider
     */ 
    public function validValueInNumericFieldYieldsResults(AcceptanceTester $I, \Codeception\Example $example)
    {
        # from examining the data, we know that all of the numeric fields do have at least
        # one beer result with the field value = 4.0...so we should see some results
        $I->fillField('dbb_' . $example['field'], '4.0');
        $I->selectOption('dbb_' . $example['field'] . '_compare', 'equals');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->seeElement('.pagination');      
        $I->dontSee('Sorry, no posts matched your criteria');
        #TODO: check actual return values
    }

    /**
     *  @return array
     */
    protected function numericFieldNameProvider()
    {
        $numeric_fields = ['abv', 'appearance', 'smell', 'taste', 'mouthfeel', 'overall'];
        $data = [];
        foreach ($numeric_fields as $f)
        {
            array_push($data, ['field' => $f]);
        }
        return $data;
    }

    // /**
    //  *  @return array
    //  */
    // protected function numericSearchFieldProvider()
    // {
    //     $numeric_fields = ['year', 'abv', 'appearance', 'smell', 'mouthfeel', 'taste', 'overall'];
    //     $test_data = [];
    //     foreach ($numeric_fields as $field) {
    //         array_push($test_data,  ['field' => $field, 'term' => 'foo', 'result' => 'Invalid search term']);
    //         array_push($test_data, ['field' => $field, 'term' => '0', 'result' => 'Results:']);
    //         array_push($test_data, ['field' => $field, 'term' => '3.0', 'result' => 'Results:']);
    //     }

    //     return $test_data;
    // }


    

}
