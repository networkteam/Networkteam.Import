<?php
namespace Networkteam\Import\Tests\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\DataProvider\ArrayDataProvider;
use Networkteam\Import\DataProvider\PagingProviderDecorator;

class PagingProviderDecoratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PagingProviderDecorator
	 */
	protected $decorator;

	public function setUp() {
		$staticProvider = new ArrayDataProvider(array(
			array('value' => 'a'),
			array('value' => 'b'),
			array('value' => 'c'),
			array('value' => 'd'),
			array('value' => 'e'),
			array('value' => 'f'),
		));

		$this->decorator = new PagingProviderDecorator($staticProvider);
	}

	/**
	 * @test
	 */
	public function decoratorWithoutOptionsWillStartFromBeginning() {
		$this->decorator->open();

		$row = $this->decorator->current();
		$this->assertEquals(array('value' => 'a'), $row);
	}

	/**
	 * @test
	 */
	public function decoratorWithOffsetOptionWillStartFromOffset() {
		$this->decorator->setOptions(array(
			PagingProviderDecorator::KEY_OFFSET => 2
		));
		$this->decorator->open();

		$row = $this->decorator->current();
		$this->assertEquals(array('value' => 'c'), $row);
	}

	/**
	 * @test
	 */
	public function decoratorWithLimitOptionWillStopAtLimit() {
		$this->decorator->setOptions(array(
			PagingProviderDecorator::KEY_LIMIT => 2
		));
		$this->decorator->open();

		$rows = iterator_to_array($this->decorator);
		$this->assertEquals(array(
			array('value' => 'a'),
			array('value' => 'b')
		), $rows);
	}

	/**
	 * @test
	 */
	public function decoratorWithOffsetAndLimitOptionWillStopAtLimit() {
		$this->decorator->setOptions(array(
			PagingProviderDecorator::KEY_OFFSET => 2,
			PagingProviderDecorator::KEY_LIMIT => 2
		));
		$this->decorator->open();

		$rows = iterator_to_array($this->decorator);
		$this->assertEquals(array(
			array('value' => 'c'),
			array('value' => 'd')
		), array_values($rows));
	}

	/**
	 * @test
	 */
	public function decoratorWithLimitLargerIteratorStopBeforeLimit() {
		$this->decorator->setOptions(array(
			PagingProviderDecorator::KEY_LIMIT => 100
		));
		$this->decorator->open();

		$rows = iterator_to_array($this->decorator);
		$this->assertCount(6, $rows);
	}

	/**
	 * @test
	 */
	public function rewindWillStartFromOffsetAgain() {
		$this->decorator->setOptions(array(
			PagingProviderDecorator::KEY_OFFSET => 2
		));
		$this->decorator->open();

		$this->decorator->next();
		$this->decorator->rewind();

		$row = $this->decorator->current();;
		$this->assertEquals(array('value' => 'c'), $row);
	}

}
