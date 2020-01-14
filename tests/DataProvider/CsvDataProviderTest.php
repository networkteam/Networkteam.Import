<?php
namespace Networkteam\Import\Tests\DataProvider;

use Networkteam\Import\DataProvider\CsvDataProvider;
use PHPUnit\Framework\TestCase;

class CsvDataProviderTest extends TestCase
{

    /**
     * @var CsvDataProvider
     */
    protected $dataProvider;

    /**
     * @var array
     */
    protected $expectedHeaders = array(
        'Header 1',
        'Header 2',
        'Header 3'
    );

    protected function setUp(): void
    {
        $this->dataProvider = new CsvDataProvider();
    }

    protected function tearDown(): void
    {
        $this->dataProvider->close();
    }

    /**
     * @test
     */
    public function headersAreMapped()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_header.csv'
        ));
        $this->dataProvider->open();
        $this->assertEquals($this->expectedHeaders, $this->dataProvider->getFieldNames(),
            'Headers should match the expected list of headers');
    }

    /**
     * @test
     */
    public function readingDataOmitsTheHeaderlines()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_header.csv'
        ));
        $this->dataProvider->open();
        $this->assertCount(2, $this->dataProvider, 'Number of data rows should match');
    }

    /**
     * @test
     */
    public function foreachIteratesAllDataRows()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_header.csv'
        ));
        $this->dataProvider->open();
        $rows = [];
        foreach ($this->dataProvider as $row) {
            $rows[] = $row;
        }

        $this->assertEquals([
            ['Header 1' => 'Value 1', 'Header 2' => 'Value 2', 'Header 3' => 'Value 3'],
            ['Header 1' => 'Value 4', 'Header 2' => '', 'Header 3' => 'Value 6']
        ], $rows);
    }

    /**
     * @test
     */
    public function readDataContainsAllAvailableFieldsEvenWithEmptyValues()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_header.csv'
        ));
        $this->dataProvider->open();
        foreach ($this->dataProvider as $row) {
            $this->assertEquals($this->expectedHeaders, array_keys($row));
        }
    }

    /**
     * @test
     */
    public function foreachWithoutHeaderIteratesAllDataRows()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_no_header.csv',
            CsvDataProvider::KEY_USE_HEADER_ROW => false
        ));
        $this->dataProvider->open();
        $rows = [];
        foreach ($this->dataProvider as $row) {
            $rows[] = $row;
        }

        $this->assertEquals([
            ['Value 1', 'Value 2', 'Value 3'],
            ['Value 4', '', 'Value 6']
        ], $rows);
    }

    /**
     * @test
     */
    public function foreachWithEmptyFileReturnsNoRows()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_empty_rows.csv'
        ));
        $this->dataProvider->open();
        $rows = [];
        foreach ($this->dataProvider as $row) {
            $rows[] = $row;
        }

        $this->assertEquals([], $rows);
    }

    /**
     * @test
     */
    public function foreachWithEmptyFileAndWithoutHeaderReturnsNoRows()
    {
        $this->dataProvider->setOptions(array(
            CsvDataProvider::KEY_FILENAME => __DIR__ . '/../fixtures/csv_dataprovider_test_empty_rows.csv',
            CsvDataProvider::KEY_USE_HEADER_ROW => false
        ));
        $this->dataProvider->open();
        $rows = [];
        foreach ($this->dataProvider as $row) {
            $rows[] = $row;
        }

        $this->assertEquals([], $rows);
    }
}
