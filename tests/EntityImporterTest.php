<?php
namespace Networkteam\Import\Tests;

class EntityImporterTest extends EntityImporterTestCase
{

    /**
     * @test
     */
    public function processImportDataWithNullEntitySkipsItem()
    {
        $dataProvider = $this->getDataProviderMock(
            [
                ['id' => 'unknown']
            ]
        );

        $importer = $this->getMockBuilder('\Networkteam\Import\EntityImporter')
            ->onlyMethods(['fetchObjectToImport', 'handleCustomProperty'])
            ->setConstructorArgs([$this->entityManager])
            ->getMock();

        $importer->expects($this->any())->method('fetchObjectToImport')->will($this->returnValue(null));

        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('beginTransaction')->once();
        $this->entityManager->shouldReceive('commit')->once();

        $importer->import($dataProvider);
    }

    /**
     * @test
     */
    public function noCommitOnDryRun()
    {
        $dataProvider = $this->getDataProviderMock(
            [
                ['id' => 'unknown']
            ]
        );
        $conn = \Mockery::mock('Doctrine\DBAL\Connection');
        $conn->shouldAllowMockingProtectedMethods();
        $conn->shouldReceive('setRollbackOnly')->once();
        $this->entityManager->shouldReceive('beginTransaction')->once();
        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('commit')->never();
        $this->entityManager->shouldReceive('getConnection')->andReturn($conn);
        $importer = new StdClassEntityImporter($this->entityManager);
        $importer->setOptions(array('dry-run' => true));
        $importer->import($dataProvider);
    }
}
