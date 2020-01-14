<?php
namespace Networkteam\Import\Tests\DataProvider;

use Networkteam\Import\DataProvider\ExcelDataProvider;
use PHPUnit\Framework\TestCase;

class BrokenExcelDataProviderTest extends TestCase
{

    /**
     * @var ExcelDataProvider
     */
    protected $dataProvider;

    /**
     * @var array
     */
    protected $expectedHeaders = array(
        'A' => 'itemid',
        'B' => 'idalt',
        'C' => 'bezeichnung',
        'D' => 'bezeichnung2',
        'E' => 'kurzbezeichnung'
    );

    protected function setUp(): void
    {
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
    public function readDataContainsOnlyValidRows()
    {
        $this->assertCount(1, $this->dataProvider);
    }

}
