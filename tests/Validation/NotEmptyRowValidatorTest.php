<?php
namespace Networkteam\Import\Tests\Validation;

use Networkteam\Import\Validation\NotEmptyRowValidator;
use PHPUnit\Framework\TestCase;

class NotEmptyRowValidatorTest extends TestCase
{

    /**
     * @var NotEmptyRowValidator
     */
    protected $validator;

    protected function setUp(): void
    {
        $this->validator = new NotEmptyRowValidator();
    }

    /**
     * @test
     * @dataProvider rowDataProvider
     */
    public function testRowData($rowValues, $expectedResult, $message, array $ignoreFields = null)
    {
        if ($ignoreFields !== null) {
            $this->validator->setIgnoreFields($ignoreFields);
        }
        $result = $this->validator->isValid($rowValues);

        $this->assertEquals($expectedResult, $result, $message);
    }

    public function rowDataProvider()
    {
        return array(
            array(
                array('v1' => 'a1', 'v2' => '', 'v3' => null, 'v4' => false),
                true,
                'Partially filled: v1 contains text and makes the array a valid row',
                null
            ),
            array(
                array('w1' => 'b1', 'w2' => 'b2'),
                true,
                'Fully filled: all rows filled, should evaluate to true',
                null
            ),
            array(
                array('x1' => '', 'x2' => false, 'x3' => null, 'x4' => ' '),
                false,
                'All empty: all row fields empty, should return false',
                null
            ),
            array(
                array('v1' => 'a1', 'v2' => '', 'v3' => null, 'v4' => false),
                false,
                'Although valid, should evaluate to false because v1 is ignored',
                array('v1')
            ),
        );
    }
}
