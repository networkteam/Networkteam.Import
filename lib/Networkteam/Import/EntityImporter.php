<?php
namespace Networkteam\Import;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Networkteam\Import\DataProvider\DataProviderInterface;

abstract class EntityImporter extends AbstractImporter {

	/**
	 * @var ObjectManager
	 */
	protected $entityManager;

	/**
	 * @var array
	 */
	protected $customProperties = array();

	protected $ignoreExceptions = FALSE;

	/**
	 * @param DataProviderInterface $dataProvider
	 * @param ObjectManager $entityManager
	 * @param EntityRepository $repository
	 */
	public function __construct(DataProviderInterface $dataProvider, ObjectManager $entityManager) {
		parent::__construct($dataProvider);
		$this->entityManager = $entityManager;
	}

	public function processImportData() {
		foreach ($this->dataProvider as $dataHash) {
			try {
				$entity = $this->processRow($dataHash);
				$this->updateResultCounter($entity);
				$this->entityManager->persist($entity);
			} catch (\Exception $e) {
				$this->importResult->addError($e->getMessage());
				if(!$this->ignoreExceptions) {
					throw $e;
				}
			}
		}
		$this->entityManager->flush();
	}

	/**
	 * @param array $object
	 */
	protected function processRow(array $dataHash) {
		$persistableObject = $this->mapValuesToObject($dataHash);

		return $persistableObject;
	}

	/**
	 * @param array $dataHash
	 * @return Object
	 */
	protected function mapValuesToObject(array $dataHash) {
		$object = $this->fetchObjectToImport($dataHash);
		$this->updateObjectFromDataHash($object, $dataHash);

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
	 * @param Object $entity
	 */
	protected function updateResultCounter($entity) {
		$state = $this->entityManager->getUnitOfWork()->getEntityState($entity);
		switch ($state) {
			case \Doctrine\ORM\UnitOfWork::STATE_NEW:
				$this->importResult->incCountImported();
				break;
			case \Doctrine\ORM\UnitOfWork::STATE_MANAGED:
				$this->importResult->incCountUpdated();
				break;
			case \Doctrine\ORM\UnitOfWork::STATE_REMOVED:
				$this->importResult->incCountDeleted();
				break;
		}
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
}
