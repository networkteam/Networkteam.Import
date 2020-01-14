<?php
namespace Networkteam\Import\Tests;

use Networkteam\Import\ImportResult;
use PHPUnit\Framework\TestCase;

class ImportResultTest extends TestCase
{

    /**
     * @test
     */
    public function incrementMethodsIncrementCounters()
    {
        $ir = new ImportResult();
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
    public function addErrorStoresErrorInArray()
    {
        $ir = new ImportResult();
        $error = 'Some Error Informtion';
        $ir->addError($error);

        $this->assertCount(1, $ir->getErrors());
    }
}
