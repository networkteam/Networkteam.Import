<?php
namespace Networkteam\Import\Tests;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

use Networkteam\Import\EntityImporter;

class StdClassEntityImporter extends EntityImporter {

	/**
	 * @param Object $object
	 * @param array $dataHash
	 * @param string $propertyName
	 * @return mixed
	 */
	protected function handleCustomProperty($object, array $dataHash, $propertyName) {

	}

	/**
	 * Implement this method and return an Object you want to be persisted
	 *
	 * @param array $dataHash
	 * @return Object
	 */
	protected function fetchObjectToImport($dataHash) {
		return new \stdClass();
	}
}