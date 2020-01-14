<?php
namespace Networkteam\Import\Tests;

use Doctrine\ORM\UnitOfWork;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityImporterTestCase extends TestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * @var MockInterface
     */
    protected $entityManager;

    /**
     * @var MockObject
     */
    protected $repository;

    /**
     * @var MockObject
     */
    protected $dataProvider;

    /**
     * @var MockObject
     */
    protected $unitOfWork;

    /**
     * @var MockObject
     */
    protected $eventManager;

    protected function setUp(): void
    {
        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(array('findOneByImportIdentifier'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $this->unitOfWork->expects($this->any())
            ->method('getEntityState')
            ->will($this->returnValue(UnitOfWork::STATE_DETACHED));

        $this->eventManager = $this->getMockBuilder('Doctrine\Common\EventManager')->getMock();

        $this->entityManager = \Mockery::mock('Doctrine\ORM\EntityManager',
            array('getUnitOfWork' => $this->unitOfWork, 'getEventManager' => $this->eventManager));
    }

    /**
     * @param array $returnValues
     * @return MockObject
     */
    protected function getDataProviderMock(array $returnValues)
    {
        $organisationTransformer = $this->getMockBuilder('Networkteam\Import\DataProvider\TransformingProviderDecorator')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->mockIterator($organisationTransformer, $returnValues);
    }

    /**
     * Setup methods required to mock an iterator
     *
     * @param MockObject $iteratorMock The mock to attach the iterator methods to
     * @param array $items The mock data we're going to use with the iterator
     * @return MockObject The iterator mock
     */
    public function mockIterator(MockObject $iteratorMock, array $items)
    {
        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return array_key_exists($iteratorData->position, $iteratorData->array);
                    }
                )
            );

        return $iteratorMock;
    }

}
