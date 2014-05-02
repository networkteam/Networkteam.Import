<?php
namespace Networkteam\Import\Tests;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class ImportResultTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 */
	public function incrementMethodsIncrementCounters() {
		$ir = new \Networkteam\Import\ImportResult();
		$ir->incCountUpdated();
		$ir->incCountImported();
		$ir->incCountDeleted();

		$this->assertEquals(1, $ir->getCountUpdated());
		$this->assertEquals(1, $ir->getCountImported());
		$this->assertEquals(1, $ir->getCountDeleted());
	}

	/**
	 * @test
	 */
	public function addErrorStoresErrorInArray() {
		$ir = new \Networkteam\Import\ImportResult();
		$error = 'Some Error Informtion';
		$ir->addError($error);

		$this->assertCount(1, $ir->getErrors());
	}
}
