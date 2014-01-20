<?php
namespace Networkteam\Import\Tests;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class AbstractImporterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 */
	public function dataProviderOpenIsCalled() {
		$nullDataProvider = $this->getDataProvider();
		$nullDataProvider->expects($this->once())
			->method('open');

		$importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', array($nullDataProvider), '', TRUE, TRUE, TRUE, array('processImportData'));

		$importer->import();
	}

	/**
	 * @test
	 */
	public function dataProviderOpenExceptionIsLogged() {
		$nullDataProvider = $this->getDataProvider();
		$nullDataProvider->expects($this->once())
			->method('open')
			->will($this->throwException(new \Networkteam\Import\Exception('Missed Open')));

		$logger = $this->getErrorLogger();

		$importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', array($nullDataProvider), '', TRUE, TRUE, TRUE, array('processImportData'));
		$importer->setLogger($logger);

		$importer->import();
	}

	/**
	 * @test
	 */
	public function dataProviderCloseExceptionIsLogged() {
		$nullDataProvider = $this->getDataProvider();
		$nullDataProvider->expects($this->once())
			->method('close')
			->will($this->throwException(new \Networkteam\Import\Exception('Missed Close')));

		$logger = $this->getErrorLogger();
		$importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', array($nullDataProvider), '', TRUE, TRUE, TRUE, array('processImportData'));
		$importer->setLogger($logger);

		$importer->import();
	}

	/**
	 * @test
	 */
	public function dataProviderCloseIsCalled() {
		$nullDataProvider = $this->getDataProvider();
		$nullDataProvider->expects($this->once())
			->method('close');

		$importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', array($nullDataProvider), '', TRUE, TRUE, TRUE, array('processImportData'));

		$importer->import();
	}

	/**
	 * @test
	 */
	public function importerProcessImportIsCalled() {
		$nullDataProvider = $this->getDataProvider();

		$importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', array($nullDataProvider), '', TRUE, TRUE, TRUE, array('processImportData'));
		$importer->expects($this->any())
			->method('processImportData');

		$importer->import();
	}

	/**
	 * @test
	 */
	public function importResultIsReturnedFromImporter() {
		$nullDataProvider = $this->getDataProvider();

		$importer = $this->getMockForAbstractClass('Networkteam\Import\AbstractImporter', array($nullDataProvider), '', TRUE, TRUE, TRUE, array('processImportData'));

		$this->assertInstanceOf('Networkteam\Import\ImportResult', $importer->import());
	}

	protected function getDataProvider() {
		return $this->getMock('Networkteam\Import\Tests\DataProvider\NullDataProvider', array('open', 'close'));
	}

	protected function getErrorLogger() {
		$logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', array(), '', TRUE, TRUE, TRUE, array('error'));
		$logger->expects($this->any())
			->method('error');
		return $logger;
	}
}
