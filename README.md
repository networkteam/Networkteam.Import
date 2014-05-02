Generic import framework
========================

Imports are based on data providers that generate or transform data. A data provider is basically an iterator returning
array values for each row in the data set. By composing data providers and transformers, more complex scenarios can be
implemented. This package provides a `TransformingProviderDecorator` that has a configurable mapping and allows to use
the Symfony expression language for custom processing (e.g. renaming or combining fields).

The abstract `EntityImporter` is a base class for imports using Doctrine ORM.

[![Build Status](https://travis-ci.org/networkteam/Networkteam.Import.png?branch=master)](https://travis-ci.org/networkteam/Networkteam.Import)

Installation
------------

Add the following require statement to the main `composer.json` file:

	"require": {
		"networkteam/import": "dev-master"
	}

Usage
-----

Extend `AbstractImporter` and implement `processImportData` with your custom import logic. For Doctrine ORM entities the
`EntityImporter` can be extended.

Example
-------

	class AddressImporter extends \Networkteam\Import\EntityImporter {

		/**
		 * @var EntityRepository
		 */
		protected $repository;

		/**
		 * List of properties for custom processing (see handleCustomProperty)
		 *
		 * @var array
		 */
		protected $customProperties = array('phone_number');

		/**
		 * @param DataProviderInterface $dataProvider
		 * @param ObjectManager $entityManager
		 * @param EntityRepository $repository
		 */
		public function __construct(DataProviderInterface $dataProvider, ObjectManager $entityManager, EntityRepository $repository) {
			parent::__construct($dataProvider, $entityManager);
			$this->repository = $repository;
		}

		/**
		 * Find an existing Address object by values from the import data or create a new one
		 *
		 * @param array $dataHash
		 * @return Address
		 */
		protected function fetchObjectToImport($dataHash) {
			$address = $this->repository->findOneByImportIdentification($dataHash['importSource'], $dataHash['externalId']);
			if ($address === NULL) {
				$address = new Address();
			}

			return $address;
		}

		/**
		 * @param Object $entity The current entity to import
		 * @param array $dataHash The full data hash of the current item
		 * @param string $propertyName The property name of a custom property
		 */
		protected function handleCustomProperty($entity, array $dataHash, $propertyName) {
			switch ($propertyName) {
				case 'phone_number':
					$object->addPhoneNumber(new PhoneNumber($dataHash[$propertyName]));
			}
		}

	}

License
-------

This package is released under the [MIT license](http://opensource.org/licenses/MIT).