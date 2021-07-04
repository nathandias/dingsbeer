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

    public function singleBeerReviewDisplays(AcceptanceTester $I)
    {
            $I->amOnPage('/beer/bashah/');
            $I->see('Bashah');
            $I->see('Nose offers plenty of hop character');
            $I->seeElement("#dbb_custom_fields");
    }

    public function commentsAppearOnSingleBeerPage (AcceptanceTester $I)
    {
        $I->amOnPage('/beer/bashah/');  # or any random single beer page
        $I->see('Submit a Comment');
        $I->seeElement('#submit');
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
        $I->dontSee('Sorry, your nonce did not verify.');
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
        $I->dontSee('Sorry, no posts matched your criteria.');
    }

    public function searchForBreweryWithSingleResult(AcceptanceTester $I)
    {
        $I->selectOption('dbb_brewery', 'Brewery AAAA');
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->see('Beer BBUU');
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

    public function searchForBreweryContainingSingleQuotes(AcceptanceTester $I)
    {
        $I->SelectOption('dbb_brewery',"%27Single+Quote+Brewery%E2%80%99");
        $I->click(['id' => 'submit']);
        $I->dontSee('Sorry, no posts matched your criteria');
        $I->seeElement('#dbb_search_results');
        $I->see('SingleQuote Beer');
        $I->dontSee('DoubleQuote Ale');
        $I->dontSee("DoubleQuote Beer");
    }

    public function searchForBreweryContainingDoubleQuotes(AcceptanceTester $I)
    {
        $I->SelectOption('dbb_brewery','%E2%80%9CDoubleQuote+Brewhaus%E2%80%9D');
        $I->click(['id' => 'submit']);
        $I->dontSee('Sorry, no posts matched your criteria');
        $I->seeElement('#dbb_search_results');
        $I->dontSee('SingleQuote Beer');
        $I->see('DoubleQuote Ale');
        $I->see("DoubleQuote Beer");
    }

    public function searchForStPetersBrewery(AcceptanceTester $I)
    {
        $I->SelectOption('dbb_brewery','St.+Peter%27s+Brewery+Co+Ltd');
        $I->click(['id' => 'submit']);
        $I->dontSee('Sorry, no posts matched your criteria');
        $I->seeElement('#dbb_search_results');
        $I->dontSee('SingleQuote Beer');
        $I->dontSee('DoubleQuote Ale');
        $I->dontSee("DoubleQuote Beer");
        // $I->see("St. Peter's Sorgham Beer");
        // $I->see("St. Peter's Winter Ale");
        // $I->see("St. Peter's Organic Ale");
        // $I->see("St. Peter's Ruby Red Ale");
        // $I->see("St. Peter's India Pale Ale");

        # TODO: understand the character encoding issues to be able to output and check for
        #    St. Peter's Sorgham Beer
        #  -or-
        #    St. Peter&#8217;s Sorgham Beer
        $I->see('St. Peter');
        $I->see("Sorgham Beer");
        $I->see("Winter Ale");
        $I->see("Organic Ale");
        $I->see("Ruby Red Ale");
        $I->see("India Pale Ale");
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

    public function searchByAllNumericFields(AcceptanceTester $I)
    {
        $data = [
            'dbb_abv' => '1.22',
            'dbb_appearance' =>'2.22',
            'dbb_smell' => '3.22',
            'dbb_taste' => '4.22',
            'dbb_mouthfeel' => '5.22',
            'dbb_overall' => '6.22',
        ];      
        
        $I->fillField('dbb_beer_name', 'Beer BB');
        $I->selectOption('dbb_beer_name_compare', 'contains');

        foreach ($data as $field => $value) {
            $I->fillField($field, $value);
            $I->selectOption("{$field}_compare", 'equals');
        }

        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        $I->see('Beer BBVV');
        
        $not_expected = ['Beer BBUU', 'Beer BBWW', 'Beer BBXX', 'Beer BBYY', 'Beer BBZZ'];
        foreach ($not_expected as $value) {
            $I->dontSee($value);
        }

    }



    /**
     * @dataprovider numericFieldTestProvider
     */
    public function searchByNumericFieldComparison(AcceptanceTester $I, \Codeception\Example $example)
    {
        $I->fillField('dbb_beer_name', 'Beer BB');
        $I->selectOption('dbb_beer_name_compare', 'contains');
        $I->fillField($example['field'], $example['value']);
        $I->selectOption($example['field'] . '_compare', $example['comparison']);
        $I->click(['id' => 'submit']);
        $I->seeElement('#dbb_search_results');
        foreach ($example['expected_results'] as $expected_result)
        {
            $I->see($expected_result);
        }
        foreach ($example['not_expected_results'] as $not_expected_result)
        {
            $I->dontSee($not_expected_result);
        }
    }

    /**
     * @return array
     */
    protected function numericFieldTestProvider()
    {
        $data = [];

        $equals_test = [
            'dbb_abv' => '1.11',
            'dbb_appearance' => '2.11',
            'dbb_smell' => '3.11',
            'dbb_taste' => '4.11',
            'dbb_mouthfeel' => '5.11',
            'dbb_overall' => '6.11',
        ];

        foreach ($equals_test as $field => $value) {
            $data[] = [
                'field' => $field,
                'value' => $value,
                'comparison' => 'equals',
                'expected_results' => ['Beer BBUU'],
                'not_expected_results' => ['Beer BBVV', 'Beer BBWW', 'Beer BBXX', 'Beer BBYY', 'Beer BBZZ'],
            ];
        }

        $greater_and_less_than_tests = [
            'dbb_abv' => '1.33',
            'dbb_appearance' => '2.33',
            'dbb_smell' => '3.33',
            'dbb_taste' => '4.33',
            'dbb_mouthfeel' => '5.33',
            'dbb_overall' => '6.33',
        ];

        foreach ($greater_and_less_than_tests as $field => $value) {
            $data[] = [
                'field' => $field,
                'value' => $value,
                'comparison' => 'greater_than',
                'expected_results' => ['Beer BBXX', 'Beer BBYY', 'Beer BBZZ'],
                'not_expected_results' => ['Beer BBUU', 'Beer BBVV', 'Beer BBWW'],
            ];
        }

        foreach ($greater_and_less_than_tests as $field => $value) {
            $data[] = [
                'field' => $field,
                'value' => $value,
                'comparison' => 'greater_than_or_equal',
                'expected_results' => ['Beer BBWW', 'Beer BBXX', 'Beer BBYY', 'Beer BBZZ'],
                'not_expected_results' => ['Beer BBUU', 'Beer BBVV'],
            ];            
        }

        foreach ($greater_and_less_than_tests as $field => $value) {
            $data[] = [
                'field' => $field,
                'value' => $value,
                'comparison' => 'less_than',
                'expected_results' => ['Beer BBUU', 'Beer BBVV'],
                'not_expected_results' => ['Beer BBWW', 'Beer BBXX', 'Beer BBYY', 'Beer BBZZ'],
            ];            
        }

        foreach ($greater_and_less_than_tests as $field => $value) {
            $data[] = [
                'field' => $field,
                'value' => $value,
                'comparison' => 'less_than_or_equal',
                'expected_results' => ['Beer BBUU', 'Beer BBVV', 'Beer BBWW'],
                'not_expected_results' => ['Beer BBXX', 'Beer BBYY', 'Beer BBZZ'],
            ];            
        }

        return $data;
    }



    

}
