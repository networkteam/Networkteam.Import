<?php
namespace Networkteam\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Networkteam\Import\DataProvider\DataProviderInterface;

abstract class EntityImporter extends AbstractImporter
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * List of fields that should not be updated via $entity->setFieldName($value)
     *
     * The method handleCustomProperty needs to implement a case for each listed field.
     *
     * @var array
     */
    protected $customProperties = [];

    /**
     * @var bool
     */
    protected $ignoreExceptions = false;

    /**
     * @var array
     */
    protected $options;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function processImportData(DataProviderInterface $dataProvider): void
    {
        $this->entityManager->beginTransaction();
        if ($this->isDryRun()) {
            $this->entityManager->getConnection()->setRollbackOnly();
        }

        try {
            $this->beforeProcessRows();

            foreach ($dataProvider as $dataHash) {
                try {
                    $entity = $this->processRow($dataHash);
                    if ($entity !== null) {
                        $this->entityManager->persist($entity);
                    }
                    $this->importResult->incCountProcessed();
                } catch (\Exception $e) {
                    $this->importResult->addError($e->getMessage());
                    if (!$this->ignoreExceptions) {
                        throw $e;
                    }
                }
            }

            $this->afterProcessRows();

            $this->entityManager->getEventManager()->addEventListener(['onFlush'], $this);

            try {
                if ($this->isDryRun()) {
                    try {
                        $this->entityManager->flush();
                    } /** @noinspection PhpRedundantCatchClauseInspection */ catch (\Doctrine\DBAL\ConnectionException $e) {
                        if ($e->getMessage() !== 'Transaction commit failed because the transaction has been marked for rollback only.') {
                            throw $e;
                        }
                    }
                } else {
                    $this->entityManager->flush();

                    $this->entityManager->commit();
                }
            } finally {
                $this->entityManager->getEventManager()->removeEventListener(['onFlush'], $this);
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }

    /**
     * @param array $dataHash
     * @return object
     */
    protected function processRow(array $dataHash)
    {
        return $this->mapValuesToObject($dataHash);
    }

    /**
     * @param array $dataHash
     * @return object
     */
    protected function mapValuesToObject(array $dataHash)
    {
        $object = $this->fetchObjectToImport($dataHash);
        if ($object !== null) {
            $this->updateObjectFromDataHash($object, $dataHash);
        }
        return $object;
    }

    /**
     * @param object $object
     * @param array $dataHash
     * @return object
     */
    protected function updateObjectFromDataHash($object, array $dataHash)
    {
        foreach ($dataHash as $propertyName => $property) {
            if (!in_array($propertyName, $this->customProperties)) {
                $this->updateProperty($object, $dataHash, $propertyName);
            } else {
                $this->handleCustomProperty($object, $dataHash, $propertyName);
            }
        }

        return $object;
    }

    /**
     * @param object $object
     * @param array $dataHash
     * @param string $propertyName
     */
    protected function updateProperty($object, array $dataHash, string $propertyName): void
    {
        list($getter, $setter) = $this->createGetterSetterForPropertyName($propertyName);
        $isExecutable = method_exists($object, $getter) && method_exists($object,
                $setter) && isset($dataHash[$propertyName]);
        if ($isExecutable) {
            $valuesDiffer = $object->$getter() !== $dataHash[$propertyName];
            if ($valuesDiffer) {
                $object->$setter($dataHash[$propertyName]);
            }
        }
    }

    /**
     * @param string $propertyName
     * @return array
     */
    protected function createGetterSetterForPropertyName($propertyName): array
    {
        $upperPropertyName = ucfirst($propertyName);
        $getter = 'get' . $upperPropertyName;
        $setter = 'set' . $upperPropertyName;

        return [$getter, $setter];
    }

    /**
     * Handle onFlush event because we need the information about "dirty" entities from Doctrine to
     * know which entities will be updated.
     *
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event): void
    {
        $unitOfWork = $event->getEntityManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->entityWillBeInserted($entity);
        }
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->entityWillBeUpdated($entity);
        }
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $this->entityWillBeDeleted($entity);
        }
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param object $object
     * @param array $dataHash
     * @param string $propertyName
     * @return mixed
     */
    abstract protected function handleCustomProperty($object, array $dataHash, string $propertyName);

    /**
     * Implement this method and return an Object you want to be persisted
     *
     * @param array $dataHash
     * @return object
     */
    abstract protected function fetchObjectToImport(array $dataHash);

    /**
     * Handle entity insertion after processing data, override for custom counting
     *
     * @param object $entity
     */
    protected function entityWillBeInserted($entity): void
    {
        $this->importResult->incCountImported();
    }

    /**
     * Handle entity update after processing data, override for custom counting
     *
     * @param object $entity
     */
    protected function entityWillBeUpdated($entity): void
    {
        $this->importResult->incCountUpdated();
    }

    /**
     * Handle entity deletion after processing data, override for custom counting
     *
     * @param object $entity
     */
    protected function entityWillBeDeleted($entity): void
    {
        $this->importResult->incCountDeleted();
    }

    /**
     * Template method to perform logic before processing rows
     */
    protected function beforeProcessRows(): void
    {
    }

    /**
     * Template method to perform logic after processing rows
     */
    protected function afterProcessRows(): void
    {
    }

    protected function isDryRun(): bool
    {
        return isset($this->options['dry-run']) && $this->options['dry-run'] === true;
    }
}
