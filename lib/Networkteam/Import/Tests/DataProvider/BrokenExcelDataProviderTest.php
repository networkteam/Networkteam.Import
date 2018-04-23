<?php
namespace Networkteam\Import\Tests\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\DataProvider\ExcelDataProvider;

class BrokenExcelDataProviderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var ExcelDataProvider
	 */
	protected $dataProvider;

	/**
	 * @var array
	 */
	protected $expectedHeaders = array(
		'A' => 'itemid', 'B' => 'idalt', 'C' => 'bezeichnung', 'D' => 'bezeichnung2', 'E' => 'kurzbezeichnung'
	);

	public function setUp() {
		if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
			$this->markTestSkipped('phpexcel not installed');
		}

		$this->dataProvider = new ExcelDataProvider();
		$this->dataProvider->setFileName(__DIR__ . '/../fixtures/excel_dataprovider_test_empty_rows.xlsx');
		$this->dataProvider->setOptions(array('excel.header_offset' => 1));
		$this->dataProvider->open();
	}

	/**
	 * @test
	 */
	public function readDataContainsOnlyValidRows() {
		$this->assertCount(1, $this->dataProvider);
	}

}
