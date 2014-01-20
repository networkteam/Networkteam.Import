<?php
namespace Networkteam\Import\Tests\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class TransformingProviderDecoratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Networkteam\KircheHamburgAddressBundle\Importer\DataProvider\TransformingProviderDecorator
	 */
	protected $transformingProviderDecorator;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $dataProvider;

	/**
	 * @var array
	 */
	protected static $dataRow;

	/**
	 * @return array
	 */
	protected function getDataRow() {
		if (self::$dataRow === NULL) {
			self::$dataRow = require_once __DIR__ . '/../fixtures/transformation_data.php';
		}

		return self::$dataRow;
	}

	public function setUp() {
		parent::setUp();
		$this->dataProvider = $this->getMockBuilder('Networkteam\Import\DataProvider\DataProviderInterface')
			->disableArgumentCloning()
			->getMock();
		$this->transformingProviderDecorator = new \Networkteam\Import\DataProvider\TransformingProviderDecorator($this->dataProvider);
	}

	/**
	 * @test
	 */
	public function implementsDataProviderInterface() {
		$this->assertInstanceOf('Networkteam\Import\DataProvider\DataProviderInterface', $this->transformingProviderDecorator);
	}

	/**
	 * @test
	 */
	public function setConfigurationIsCallable() {
		$configuration = array();
		$this->transformingProviderDecorator->setMapping($configuration);
	}

	/**
	 * @test
	 */
	public function dataIsTransformedAccordingConfiguration() {
		$configuration = array(
			'firstName' => 'vorname',
			'lastName' => 'bezeichnung'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->assertEquals(array(
			'firstName' => 'Harald',
			'lastName' => 'Satsch'
		), $this->transformingProviderDecorator->current());
	}

	/**
	 * @test
	 * @expectedException \Networkteam\Import\Exception\ConfigurationException
	 * @expectedExceptionMessage The key "does_not_exist" was not found in the list of keys: bezeichnung, vorname, email-adresse
	 */
	public function exceptionIsThrownForInvalidConfiguration() {
		$configuration = array(
			'firstName' => 'does_not_exist',
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->transformingProviderDecorator->current();
	}

	/**
	 * @test
	 */
	public function configurationCanContainExpressions() {
		$configuration = array(
			'firstName' => 'vorname',
			'lastName' => 'bezeichnung',
			'parent' => '${\'1234\'}'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->transformingProviderDecorator->current();
	}

	/**
	 * @test
	 */
	public function expressionsAreEvaluatedCorrectly() {
		$configuration = array(
			'firstName' => 'vorname',
			'lastName' => 'bezeichnung',
			'parent' => '${\'1234\'}',
			'name' => '${row["vorname"] ~ \' \' ~ row["bezeichnung"]}'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->assertEquals(array(
			'firstName' => 'Harald',
			'lastName' => 'Satsch',
			'parent' => '1234',
			'name' => 'Harald Satsch'
		), $this->transformingProviderDecorator->current());
	}
}
