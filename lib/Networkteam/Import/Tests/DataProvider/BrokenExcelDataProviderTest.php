<?php
namespace Networkteam\KircheHamburgAddressBundle\Tests\Importer\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class BrokenExcelDataProviderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Networkteam\Import\DataProvider\ExcelDataProvider
	 */
	protected $dataProvider;

	/**
	 * @var array
	 */
	protected $expectedHeaders = array(
		'A' => 'itemid', 'B' => 'idalt', 'C' => 'bezeichnung', 'D' => 'bezeichnung2', 'E' => 'kurzbezeichnung'
	);

	public function setUp() {
		parent::setUp();
		$this->dataProvider = new \Networkteam\Import\DataProvider\ExcelDataProvider();
		$this->dataProvider->setFileName(__DIR__ . '/../fixtures/excel_dataprovider_test_empty_rows.xlsx');
		$this->dataProvider->open();
		$this->dataProvider->setOptions(array('excel.header_offset' => 1));
	}

	/**
	 * @test
	 */
	public function readDataContainsOnlyValidRows() {
		$expectedRowCount = 1;
		$rowCount = 0;
		foreach ($this->dataProvider as $row) {
			$rowCount++;
			if($rowCount > 1) {
				break;
			}
		}
		$this->assertEquals($expectedRowCount, $rowCount);
	}
}
