<?php
namespace Networkteam\Import\Tests\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\DataProvider\ExcelDataProvider;

class ExcelDataProviderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ExcelDataProvider
	 */
	protected $dataProvider;

	/**
	 * @var array
	 */
	protected $expectedOrganisationHeaders = array(
		'itemid', 'idalt', 'bezeichnung', 'bezeichnung2', 'straße'
	);

	public function setUp() {
		if (!class_exists('\PHPExcel')) {
			$this->markTestSkipped('phpexcel not installed');
		}

		$this->dataProvider = new ExcelDataProvider();
		$this->dataProvider->setFileName(__DIR__ . '/../fixtures/excel_dataprovider_test.xlsx');
		$this->dataProvider->setOptions(array('excel.header_offset' => 2));
		$this->dataProvider->open();
	}

	/**
	 * @test
	 * @expectedException \Networkteam\Import\Exception
	 * @expectedExceptionCode 1399470312
	 */
	public function setOptionsFailsAfterOpen() {
		$this->dataProvider->setOptions(array('foo' => 'bar'));
	}

	/**
	 * @test
	 */
	public function organisationHeadersAreMapped() {
		$this->assertEquals(array_values($this->expectedOrganisationHeaders), $this->dataProvider->getFieldNames(), 'Headers should match the expected list of headers');
	}

	/**
	 * @test
	 */
	public function readingDataOmitsTheHeaderlines() {
		$this->assertCount(3, $this->dataProvider, 'Number of data rows should match');
	}

	/**
	 * @test
	 */
	public function readDataContainsAllAvailableFieldsEvenWithNullValues() {
		foreach ($this->dataProvider as $row) {
			$this->assertEquals(array_values($this->expectedOrganisationHeaders), array_keys($row));
		}
	}

}
