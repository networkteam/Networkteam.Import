<?php
namespace Networkteam\KircheHamburgAddressBundle\Tests\Importer\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class ExcelDataProviderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Networkteam\Import\DataProvider\ExcelDataProvider
	 */
	protected $dataProvider;

	/**
	 * @var array
	 */
	protected $expectedOrganisationHeaders = array(
		'A' => 'itemid', 'B' => 'idalt', 'C' => 'bezeichnung', 'D' => 'bezeichnung2', 'E' => 'kurzbezeichnung'
	);

	public function setUp() {
		parent::setUp();
		$this->dataProvider = new \Networkteam\Import\DataProvider\ExcelDataProvider();
		$this->dataProvider->setFileName(__DIR__ . '/../fixtures/excel_dataprovider_test.xlsx');
		$this->dataProvider->open();
		$this->dataProvider->setOptions(array('excel.header_offset' => 2));
	}

	/**
	 * @test
	 */
	public function organisationHeadersAreMapped() {
		$this->assertEquals($this->expectedOrganisationHeaders, $this->dataProvider->getFieldNames(), 'Headers should match the expected list of headers');
	}

	/**
	 * @test
	 */
	public function readingDataOmitsTheHeaderlines() {
		$expectedRowCount = 3;
		$rowCount = 0;
		foreach ($this->dataProvider as $row) {
			$rowCount++;
		}

		$this->assertEquals($expectedRowCount, $rowCount, 'The number of DataRows should be: ' . $expectedRowCount);
	}

	/**
	 * @test
	 */
	public function readDataContainsAllAvailableFieldsEvenWithNullValues() {
		$expectedRowCount = 3;
		$rowCount = 0;
		foreach ($this->dataProvider as $row) {
			$rowCount++;
		}
		$this->assertEquals(array_values($this->expectedOrganisationHeaders), array_keys($row));
	}
}
