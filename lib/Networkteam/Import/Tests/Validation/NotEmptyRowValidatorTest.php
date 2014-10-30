<?php
namespace Networkteam\Import\Tests\Validation;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\Validation\NotEmptyRowValidator;

class NotEmptyRowValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var NotEmptyRowValidator
	 */
	protected $validator;

	public function setUp() {
		$this->validator = new NotEmptyRowValidator();
	}

	/**
	 * @test
	 * @dataProvider rowDataProvider
	 */
	public function testRowData($rowValues, $expectedResult, $message, array $ignoreFields = NULL) {
		if ($ignoreFields !== NULL) {
			$this->validator->setIgnoreFields($ignoreFields);
		}
		$result = $this->validator->isValid($rowValues);

		$this->assertEquals($expectedResult, $result, $message);
	}

	public function rowDataProvider() {
		return array(
			array(
				array('v1' => 'a1', 'v2' => '', 'v3' => NULL, 'v4' => FALSE), TRUE, 'Partially filled: v1 contains text and makes the array a valid row', NULL
			),
			array(
				array('w1' => 'b1', 'w2' => 'b2'), TRUE, 'Fully filled: all rows filled, should evaluate to true', NULL
			),
			array(
				array('x1' => '', 'x2' => FALSE, 'x3' => NULL, 'x4' => ' '), FALSE, 'All empty: all row fields empty, should return false', NULL
			),
			array(
				array('v1' => 'a1', 'v2' => '', 'v3' => NULL, 'v4' => FALSE), FALSE, 'Although valid, should evaluate to false because v1 is ignored', array('v1')
			),
		);
	}
}
