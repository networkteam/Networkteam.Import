<?php
namespace Networkteam\Import\Tests\DataProvider;

use Networkteam\Import\DataProvider\ArrayDataProvider;
use Networkteam\Import\DataProvider\RowValidationDecorator;
use Networkteam\Import\Validation\NotEmptyRowValidator;

class RowValidationDecoratorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @test
     */
    public function testWithEmptyRowValidatorStart()
    {
        $testData = array(
            array('key1' => '', 'key2' => ''), //empty row
            array('key1' => 'value1', 'key2' => 'value2'),
            array('key1' => '', 'key2' => 'value2'),
            array('key1' => 'value1', 'key2' => ''),
        );
        $testResults = array();
        $dataProvider = new ArrayDataProvider($testData);
        $rowValidator = new RowValidationDecorator($dataProvider);
        $rowValidator->setValidator(new NotEmptyRowValidator());

        $expectedResults = $testData;
        unset($expectedResults[0]);
        $expectedResults = array_values($expectedResults);

        foreach ($rowValidator as $values) {
            $testResults[] = $values;
        }

        $this->assertEquals($expectedResults, $testResults, 'Both arrays should be equal and not contains 1st row');
    }

    /**
     * @test
     */
    public function testWithEmptyRowValidatorMiddle()
    {
        $testData = array(
            array('key1' => 'value1', 'key2' => 'value2'),
            array('key1' => '', 'key2' => 'value2'),
            array('key1' => '', 'key2' => ''), //empty row
            array('key1' => 'value1', 'key2' => ''),
        );
        $testResults = array();
        $dataProvider = new ArrayDataProvider($testData);
        $rowValidator = new RowValidationDecorator($dataProvider);
        $rowValidator->setValidator(new NotEmptyRowValidator());

        $expectedResults = $testData;
        unset($expectedResults[2]);
        $expectedResults = array_values($expectedResults);

        foreach ($rowValidator as $values) {
            $testResults[] = $values;
        }

        $this->assertEquals($expectedResults, $testResults, 'Both arrays should be equal and not contains 3rd row');
    }

    /**
     * @test
     */
    public function testWithEmptyRowValidatorEnd()
    {
        $testData = array(
            array('key1' => 'value1', 'key2' => 'value2'),
            array('key1' => '', 'key2' => 'value2'),
            array('key1' => 'value1', 'key2' => ''),
            array('key1' => '', 'key2' => ''), //empty row
        );
        $testResults = array();
        $dataProvider = new ArrayDataProvider($testData);
        $rowValidator = new RowValidationDecorator($dataProvider);
        $rowValidator->setValidator(new NotEmptyRowValidator());

        $expectedResults = $testData;
        unset($expectedResults[3]);
        $expectedResults = array_values($expectedResults);

        foreach ($rowValidator as $values) {
            $testResults[] = $values;
        }

        $this->assertEquals($expectedResults, $testResults, 'Both arrays should be equal and not contains 5th row');
    }

    /**
     * @test
     */
    public function testWithEmptyRowValidatorDoubleEmptyEnd()
    {
        $testData = array(
            array('key1' => 'value1', 'key2' => 'value2'),
            array('key1' => '', 'key2' => 'value2'),
            array('key1' => 'value1', 'key2' => ''),
            array('key1' => '', 'key2' => ''), //empty row
            array('key1' => '', 'key2' => ''), //empty row
        );
        $testResults = array();
        $dataProvider = new ArrayDataProvider($testData);
        $rowValidator = new RowValidationDecorator($dataProvider);
        $rowValidator->setValidator(new NotEmptyRowValidator());

        $expectedResults = $testData;
        unset($expectedResults[3]);
        unset($expectedResults[4]);
        $expectedResults = array_values($expectedResults);

        foreach ($rowValidator as $values) {
            $testResults[] = $values;
        }

        $this->assertEquals($expectedResults, $testResults, 'Both arrays should be equal and not contains 5th row');
    }
}