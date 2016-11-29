
# Basic import


Assume we have an entity Address which should be created or updated by an Excel import. 

```php
	class Address {
		/**
		 * @var string
		 */
		protected $street;
	
		/**
		 * @var string
		 */
		protected $zip;
	
		/**
		 * @var string
		 */
		protected $city;
	
		/**
		 * @var PhoneNumber
		 */
		protected $phoneNumber;
		
		// ... getter and setter here
	}
	
	class PhoneNumber {
		/**
		 * @var string
		 */
		protected $countryPrefix;
	
		/**
		 * @var string
		 */
		protected $number;
		
		// ... getter and setter here
	}
```
## The data source

The source is an Excel file containing the following fields:

|source_id|street        |zip  |city     |phone_number |
|---------|--------------|-----|---------|-------------|
|100      |Gardenstreet 1|23445|Baltimore|+494080021345|
|120      |Rosestreet 212|24103|Hamburg  |+494080021346|
|140      |Bakerstreet 80|43534|London   |+494080021347|

```php

	use Networkteam\Import\DataProvider\ExcelDataProvider;

	$dataProvider = new ExcelDataProvider();
	$dataProvider->setOptions(array(
		ExcelDataProvider::KEY_FILENAME => '/path/to/your/excel/file.xls',
	));
```

## Handle custom properties

The problem here is to create an extra instance PhoneNumber for each entity. 
The name of the property to handle has to be placed in the *$customProperties* 
property of the importer. This is used to know which properties to skip in the default path of importing properties.

```php
		protected $customProperties = array('phone_number');
```
 To treat the property correctly implement the method *handleCustomProperty* like this.

```php

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
```


## Loading an existing entity for update

The method *fetchObjectToImport* is responsible for creating the object which will be filled up with the values from the import source. It is also the place to look into the database when updating an object.

```php
	/**
	 * Find an existing Address object by values from the import data or create a new one
	 *
	 * @param array $dataHash
	 * @return Address
	 */
	protected function fetchObjectToImport($dataHash) {
		$address = $this->repository->findOneBySourceId($dataHash['source_id']);
		if ($address === NULL) {
			$address = new Address();
		}

		return $address;
	}
```

## Complete Importer
To get a complete look at the details here is the complete importer class.

```php
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
			$address = $this->repository->findOneBySourceId($dataHash['source_id']);
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
```


## Combining the Importer and DataProvider

The Import is now ready to process data.

```php
$entityManager = $someContainer->getEntitiyManager();

$importer = new AddressImporter($dataProvider, $entityManger);
$importer->import();

```
