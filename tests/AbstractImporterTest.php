<?php
namespace Networkteam\Import\Tests;

use PHPUnit\Framework\TestCase;

class AbstractImporterTest extends TestCase
{

    /**
     * @test
     */
    public function dataProviderOpenIsCalled()
    {
        $nullDataProvider = $this->getDataProvider();
        $nullDataProvider->expects($this->once())
            ->method('open');

        $importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', [], '', true, true, true,
            ['processImportData']);

        $importer->import($nullDataProvider);
    }

    /**
     * @test
     */
    public function dataProviderOpenExceptionIsLogged()
    {
        $nullDataProvider = $this->getDataProvider();
        $nullDataProvider->expects($this->once())
            ->method('open')
            ->will($this->throwException(new \Networkteam\Import\Exception('Missed Open')));

        $logger = $this->getErrorLogger();

        $importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', [], '', true, true, true,
            ['processImportData']);
        $importer->setLogger($logger);

        $importer->import($nullDataProvider);
    }

    /**
     * @test
     */
    public function dataProviderCloseExceptionIsLogged()
    {
        $nullDataProvider = $this->getDataProvider();
        $nullDataProvider->expects($this->once())
            ->method('close')
            ->will($this->throwException(new \Networkteam\Import\Exception('Missed Close')));

        $logger = $this->getErrorLogger();
        $importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', [], '', true, true, true,
            ['processImportData']);
        $importer->setLogger($logger);

        $importer->import($nullDataProvider);
    }

    /**
     * @test
     */
    public function dataProviderCloseIsCalled()
    {
        $nullDataProvider = $this->getDataProvider();
        $nullDataProvider->expects($this->once())
            ->method('close');

        $importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', [], '', true, true, true,
            ['processImportData']);

        $importer->import($nullDataProvider);
    }

    /**
     * @test
     */
    public function importerProcessImportIsCalled()
    {
        $nullDataProvider = $this->getDataProvider();

        $importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', [], '', true, true, true,
            ['processImportData']);
        $importer->expects($this->once())
            ->method('processImportData');

        $importer->import($nullDataProvider);
    }

    /**
     * @test
     */
    public function importResultIsReturnedFromImporter()
    {
        $nullDataProvider = $this->getDataProvider();

        $importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', [], '', true, true, true,
            ['processImportData']);

        $importResult = $importer->import($nullDataProvider);

        $this->assertInstanceOf('Networkteam\Import\ImportResult', $importResult);
    }

    protected function getDataProvider()
    {
        return $this->getMockBuilder('Networkteam\Import\Tests\DataProvider\NullDataProvider')->onlyMethods([
            'open',
            'close'
        ])->getMock();
    }

    protected function getErrorLogger()
    {
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', [], '', true, true, true,
            ['error']);
        $logger->expects($this->any())
            ->method('error');
        return $logger;
    }
}
