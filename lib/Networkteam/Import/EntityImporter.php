<?php
namespace Networkteam\Import;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Doctrine\ORM\EntityManager;
use Networkteam\Import\DataProvider\DataProviderInterface;

abstract class EntityImporter extends AbstractImporter {

	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @var array
	 */
	protected $customProperties = array();

	/**
	 * @var boolean
	 */
	protected $ignoreExceptions = FALSE;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @param DataProviderInterface $dataProvider
	 * @param EntityManager $entityManager
	 */
	public function __construct(DataProviderInterface $dataProvider, EntityManager $entityManager) {
		parent::__construct($dataProvider);
		$this->entityManager = $entityManager;
	}

	public function processImportData() {
		if ($this->isDryRun()) {
			$this->entityManager->beginTransaction();
			$this->entityManager->getConnection()->setRollbackOnly();
		}

		$this->beforeProcessRows();

		foreach ($this->dataProvider as $dataHash) {
			try {
				$entity = $this->processRow($dataHash);
				if ($entity !== NULL) {
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

		$this->entityManager->getEventManager()->addEventListener(array('onFlush'), $this);
		if ($this->isDryRun()) {
			try {
				$this->entityManager->flush();
			} catch (\Doctrine\DBAL\ConnectionException $e) {
				if ($e->getMessage() !== 'Transaction commit failed because the transaction has been marked for rollback only.') {
					throw $e;
				}
			}
		} else {
			$this->entityManager->flush();
		}
		$this->entityManager->getEventManager()->removeEventListener(array('onFlush'), $this);
	}

	/**
	 * @param array $dataHash
	 * @return Object
	 */
	protected function processRow(array $dataHash) {
		return $this->mapValuesToObject($dataHash);
	}

	/**
	 * @param array $dataHash
	 * @return Object
	 */
	protected function mapValuesToObject(array $dataHash) {
		$object = $this->fetchObjectToImport($dataHash);
		if ($object !== NULL) {
			$this->updateObjectFromDataHash($object, $dataHash);
		}
		return $object;
	}

	/**
	 * @param Object $object
	 * @param array $dataHash
	 * @return Object
	 */
	protected function updateObjectFromDataHash($object, array $dataHash) {
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
	 * @param Object $object
	 * @param array $dataHash
	 * @param string $propertyName
	 */
	protected function updateProperty($object, $dataHash, $propertyName) {
		list($getter, $setter) = $this->createGetterSetterForPropertyName($propertyName);
		$isExecutable = method_exists($object, $getter) && method_exists($object, $setter) && isset($dataHash[$propertyName]);
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
	protected function createGetterSetterForPropertyName($propertyName) {
		$getter = 'get' . ucfirst($propertyName);
		$setter = 'set' . ucfirst($propertyName);

		return array($getter, $setter);
	}

	/**
	 * Handle onFlush event because we need the information about "dirty" entities from Doctrine to
	 * know which entities will be updated.
	 *
	 * @param \Doctrine\ORM\Event\OnFlushEventArgs $event
	 */
	public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $event) {
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
	public function setOptions(array $options) {
		$this->options = $options;
	}

	/**
	 * @param Object $object
	 * @param array $dataHash
	 * @param string $propertyName
	 * @return mixed
	 */
	abstract protected function handleCustomProperty($object, array $dataHash, $propertyName);

	/**
	 * Implement this method and return an Object you want to be persisted
	 *
	 * @param array $dataHash
	 * @return Object
	 */
	abstract protected function fetchObjectToImport($dataHash);

	/**
	 * Handle entity insertion after processing data, override for custom counting
	 *
	 * @param Object $entity
	 */
	protected function entityWillBeInserted($entity) {
		$this->importResult->incCountImported();
	}

	/**
	 * Handle entity update after processing data, override for custom counting
	 *
	 * @param Object $entity
	 */
	protected function entityWillBeUpdated($entity) {
		$this->importResult->incCountUpdated();
	}

	/**
	 * Handle entity deletion after processing data, override for custom counting
	 *
	 * @param Object $entity
	 */
	protected function entityWillBeDeleted($entity) {
		$this->importResult->incCountDeleted();
	}

	/**
	 * Template method to perform logic before processing rows
	 */
	protected function beforeProcessRows() {}

	/**
	 * Template method to perform logic after processing rows
	 */
	protected function afterProcessRows() {}

	/**
	 * @return bool
	 */
	protected function isDryRun() {
		return isset($this->options['dry-run']) && $this->options['dry-run'] === TRUE;
	}
}
