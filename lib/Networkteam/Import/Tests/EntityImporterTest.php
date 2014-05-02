<?php
namespace Networkteam\Import\Tests;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class EntityImporterTest extends EntityImporterTestCase {

	/**
	 * @test
	 */
	public function processImportDataWithNullEntitySkipsItem() {
		$dataProvider = $this->getDataProviderMock(array(array('id' => 'unknown')));

		$importer = $this->getMockBuilder('\Networkteam\Import\EntityImporter')->setMethods(array('fetchObjectToImport', 'handleCustomProperty'))->setConstructorArgs(array($dataProvider, $this->entityManager))->getMock();

		$importer->expects($this->any())->method('fetchObjectToImport')->will($this->returnValue(NULL));

		$this->entityManager->shouldReceive('flush');
		$this->entityManager->shouldReceive('persist')->never();

		$importer->processImportData();
	}

}
