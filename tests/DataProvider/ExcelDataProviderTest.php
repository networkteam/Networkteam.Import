<?php
namespace Networkteam\Import\Tests\DataProvider;

use Networkteam\Import\DataProvider\ExcelDataProvider;

class ExcelDataProviderTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ExcelDataProvider
     */
    protected $dataProvider;

    /**
     * @var array
     */
    protected $expectedOrganisationHeaders = array(
        'itemid',
        'idalt',
        'bezeichnung',
        'bezeichnung2',
        'straße'
    );

    protected function setUp(): void
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            $this->markTestSkipped('phpexcel not installed');
        }

        $this->dataProvider = new ExcelDataProvider();
        $this->dataProvider->setFileName(__DIR__ . '/../fixtures/excel_dataprovider_test.xlsx');
        $this->dataProvider->setOptions(array(ExcelDataProvider::KEY_HEADER_OFFSET => 2));
    }

    /**
     * @test
     */
    public function setOptionsFailsAfterOpen()
    {
        $this->expectException(\Networkteam\Import\Exception::class);
        $this->expectExceptionCode(1399470312);

        $this->dataProvider->open();
        $this->dataProvider->setOptions(array('foo' => 'bar'));
    }

    /**
     * @test
     */
    public function organisationHeadersAreMapped()
    {
        $this->dataProvider->open();
        $this->assertEquals($this->expectedOrganisationHeaders, $this->dataProvider->getFieldNames(),
            'Headers should match the expected list of headers');
    }

    /**
     * @test
     */
    public function readingDataOmitsTheHeaderlines()
    {
        $this->dataProvider->open();
        $this->assertCount(3, $this->dataProvider, 'Number of data rows should match');
    }

    /**
     * @test
     */
    public function optionHeaderPositionSetsRowForFieldNames()
    {
        $this->dataProvider->setOptions(array(ExcelDataProvider::KEY_HEADER_POSITION => 1));
        $this->dataProvider->open();
        $this->assertEquals(array('itemid', 'id im altsystem', 'bezeichnung', 'bezeichnung teil2', 'straße'),
            $this->dataProvider->getFieldNames(), 'Headers should match the expected list of headers');
    }

    /**
     * @test
     */
    public function readDataContainsAllAvailableFieldsEvenWithNullValues()
    {
        $this->dataProvider->open();
        foreach ($this->dataProvider as $row) {
            $this->assertEquals($this->expectedOrganisationHeaders, array_keys($row));
        }
    }

}
